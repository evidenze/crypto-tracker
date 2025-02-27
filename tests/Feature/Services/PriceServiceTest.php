<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PriceService;
use App\Models\PriceAggregate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PriceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $priceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->priceService = new PriceService();
    }

    /**
     * Test the getLatestPrices method.
     */
    public function test_get_latest_prices()
    {
        // Seed the database with test data
        PriceAggregate::factory()->create([
            'pair' => 'BTC/USD',
            'price' => 50000,
            'timestamp' => now()->subMinutes(5),
        ]);
        PriceAggregate::factory()->create([
            'pair' => 'ETH/USD',
            'price' => 3000,
            'timestamp' => now()->subMinutes(3),
        ]);
        PriceAggregate::factory()->create([
            'pair' => 'BTC/USD',
            'price' => 51000,
            'timestamp' => now(),
        ]);

        // Call the method
        $prices = $this->priceService->getLatestPrices();

        // Assert the results
        $this->assertCount(2, $prices); // Only unique pairs
        $this->assertEquals('BTC/USD', $prices[0]->pair);
        $this->assertEquals(51000, $prices[0]->price); // Latest price for BTC/USD
        $this->assertEquals('ETH/USD', $prices[1]->pair);

        // Assert caching
        $cachedPrices = Cache::get('latest_prices');
        $this->assertEquals($prices, $cachedPrices);
    }

    /**
     * Test the getPriceHistory method.
     */
    public function test_get_price_history()
    {
        // Seed the database with test data
        PriceAggregate::factory()->create([
            'pair' => 'BTC/USD',
            'price' => 50000,
            'timestamp' => now()->subMinutes(10),
        ]);
        PriceAggregate::factory()->create([
            'pair' => 'BTC/USD',
            'price' => 51000,
            'timestamp' => now()->subMinutes(5),
        ]);
        PriceAggregate::factory()->create([
            'pair' => 'BTC/USD',
            'price' => 52000,
            'timestamp' => now(),
        ]);

        // Call the method
        $history = $this->priceService->getPriceHistory('BTC/USD');

        // Assert the results
        $this->assertCount(3, $history);
        $this->assertEquals('BTC/USD', $history[0]->pair);
        $this->assertEquals(52000, $history[0]->price); // Latest price first

        // Assert caching
        $cachedHistory = Cache::get('price_history_BTC/USD');
        $this->assertEquals($history, $cachedHistory);
    }

    /**
     * Test the getConfiguration method.
     */
    public function test_get_configuration()
    {
        // Mock the configuration values
        Config::set('crypto.pairs', ['BTC/USD', 'ETH/USD']);
        Config::set('crypto.exchanges', ['Binance', 'Coinbase']);
        Config::set('crypto.interval', 60);

        // Call the method
        $config = $this->priceService->getConfiguration();

        // Assert the results
        $this->assertEquals([
            'pairs' => ['BTC/USD', 'ETH/USD'],
            'exchanges' => ['Binance', 'Coinbase'],
            'interval' => 60,
        ], $config);
    }
}