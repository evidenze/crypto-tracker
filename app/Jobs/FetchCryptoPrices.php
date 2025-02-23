<?php

namespace App\Jobs;

use App\Events\PriceUpdated;
use App\Models\PriceAggregate;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FetchCryptoPrices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $pairs;
    private array $exchanges;
    private int $interval;
    private int $maxRetries = 3;

    public function __construct()
    {
        $this->pairs = config('crypto.pairs', []);
        $this->exchanges = config('crypto.exchanges', []);
        $this->interval = config('crypto.interval', 5);
    }

    public function handle()
    {
        
        $lock = Cache::lock('crypto_price_fetcher_lock', $this->interval);
        if (!$lock->get()) {
            return;
        }


        try {
            // Get API token only when processing
            $apiToken = config('crypto.api_token');

            $client = new Client([
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiToken
                ]
            ]);

            // Create promises for parallel requests
            $promises = [];
            foreach ($this->exchanges as $exchange) {
                $cacheKey = "prices_{$exchange}_" . implode('_', $this->pairs);
                
                if ($cachedData = Cache::get($cacheKey)) {
                    $promises[$exchange] = Promise\Create::promiseFor($cachedData);
                    continue;
                }

                $symbols = $this->buildSymbolsString($exchange);

               // Manually construct the query string to preserve the `+` character
                $queryString = http_build_query([
                    'symbol' => $symbols,
                    'token' => $apiToken
                ]);

                // Replace `%2B` with `+` in the query string
                $queryString = str_replace('%2B', '+', $queryString);

                // Construct the full URL
                $url = "https://api.freecryptoapi.com/v1/getData?{$queryString}";

                $promises[$exchange] = $client->getAsync($url)->then(
                    function ($response) use ($exchange, $cacheKey) {
                        $data = json_decode($response->getBody()->getContents(), true);
                        if (!isset($data['symbols']) || $data['status'] !== 'success') {
                            throw new \Exception("Invalid response from {$exchange}");
                        }
                        Cache::put($cacheKey, $data, now()->addSeconds(30));
                        return $data;
                    },
                    function ($exception) use ($exchange) {
                        return null;
                    }
                );
            }

            // Wait for all promises to complete
            $responses = Promise\Utils::settle($promises)->wait();

            // Process responses and calculate averages
            $priceData = collect();
            foreach ($responses as $exchange => $response) {
                if ($response['state'] === 'fulfilled' && $response['value']) {
                    $symbols = $response['value']['symbols'];
                    foreach ($symbols as $symbol) {
                        $priceData->push([
                            'exchange' => $exchange,
                            'pair' => $symbol['symbol'],
                            'price' => (float) $symbol['last'],
                            'change_percentage' => (float) $symbol['daily_change_percentage'],
                            'highest' => (float) $symbol['highest'],
                            'lowest' => (float) $symbol['lowest'],
                            'timestamp' => now()
                        ]);
                    }
                }
            }

            // Calculate averages and broadcast
            $averages = $priceData->groupBy('pair')->map(function ($groupedPrices) {
                $validPrices = $groupedPrices->pluck('price')->filter();
                if ($validPrices->isEmpty()) {
                    return null;
                }

                $avgPrice = $validPrices->avg();
                
                $priceAggregate = PriceAggregate::create([
                    'pair' => $groupedPrices->first()['pair'],
                    'price' => $avgPrice,
                    'change_percentage' => $groupedPrices->pluck('change_percentage')->avg(),
                    'highest' => $groupedPrices->pluck('highest')->max(),
                    'lowest' => $groupedPrices->pluck('lowest')->min(),
                    'exchanges' => $groupedPrices->pluck('exchange')->toArray(),
                    'timestamp' => now()
                ]);

                broadcast(new PriceUpdated($priceAggregate));

                return $priceAggregate;
            })->filter();

        } catch (\Exception $e) {
            if ($this->attempts() < $this->maxRetries) {
                $this->release(5);
            }
        } finally {
            $lock->release();
            // if ($this->attempts() === 0) {
            //     self::dispatch()->delay(now()->addSeconds($this->interval));
            // }
        }
    }

    private function buildSymbolsString(string $exchange): string
    {
        // Join all pairs with + and append the exchange at the end
        return implode('+', $this->pairs) . '@' . $exchange;
    }
}