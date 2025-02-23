<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\FetchCryptoPrices;
use App\Models\PriceAggregate;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class FetchCryptoPricesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set the cache driver to 'array'
        config(['cache.default' => 'array']);

        // Mock configuration values
        config([
            'crypto.pairs' => ['BTCUSD', 'ETHUSD'],
            'crypto.exchanges' => ['binance', 'kraken'],
            'crypto.interval' => 5,
            'crypto.api_token' => 'test-token',
        ]);
    }


    #[Test]
    public function it_handles_api_failures()
    {
        // Mock Cache facade consistently using Mockery
        Cache::shouldReceive('lock')
            ->andReturn(Mockery::mock([
                'get' => true,
                'release' => true
            ]));
        Cache::shouldReceive('get')->andReturnNull();
        Cache::shouldReceive('put')->never();
        Cache::shouldReceive('has')->andReturnFalse();
    
        // Mock Guzzle client to simulate API failure
        $mock = new MockHandler([
            new Response(500, [], 'Internal Server Error'),
        ]);
    
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
    
        $this->app->instance(Client::class, $client);
    
        // Dispatch the job
        $job = new FetchCryptoPrices();
        $job->handle();
    
        // Assert that no price aggregates were saved
        $this->assertCount(0, PriceAggregate::all());
    }

    #[Test]
    public function it_handles_invalid_responses()
    {
        // Mock Guzzle client to return invalid response
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'status' => 'error',
                'message' => 'Invalid symbols',
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->app->instance(Client::class, $client);

        // Dispatch the job
        $job = new FetchCryptoPrices();
        $job->handle();

        // Assert that no price aggregates were saved
        $this->assertCount(0, PriceAggregate::all());
    }

    #[Test]
    public function it_uses_cached_data_when_available()
    {
        // Mock cached data
        $cachedData = [
            'status' => 'success',
            'symbols' => [
                ['symbol' => 'BTCUSD', 'last' => 50000, 'daily_change_percentage' => 1.5, 'highest' => 51000, 'lowest' => 49000],
            ],
        ];

        Cache::put('prices_binance_BTCUSD_ETHUSD', $cachedData, now()->addSeconds(30));

        // Mock Guzzle client (should not be called)
        $mock = new MockHandler([]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->app->instance(Client::class, $client);

        // Dispatch the job
        $job = new FetchCryptoPrices();
        $job->handle();

        // Assert that the cached data was used
        $this->assertCount(1, PriceAggregate::all());
    }

    #[Test]
    public function it_retries_on_failure()
    {
        // Mock Guzzle client to simulate API failure
        $mock = new MockHandler([
            new Response(500, [], 'Internal Server Error'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->app->instance(Client::class, $client);

        // Dispatch the job
        $job = new FetchCryptoPrices();
        $job->handle();

        // Assert that the job was released for retry
        $this->assertEquals(1, $job->attempts());
    }
}