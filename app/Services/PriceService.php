<?php

namespace App\Services;

use App\Models\PriceAggregate;
use Illuminate\Support\Facades\Cache;

class PriceService
{
    /**
     * Get the latest prices for all configured pairs.
     */
    public function getLatestPrices()
    {
        return Cache::remember('latest_prices', 5, function () {
            return PriceAggregate::query()
                ->select(['pair', 'price', 'change_percentage', 'highest', 'lowest', 'timestamp', 'exchanges'])
                ->orderBy('timestamp', 'desc')
                ->get()
                ->unique('pair')
                ->values();
        });
    }

    /**
     * Get historical prices for a specific pair.
     */
    public function getPriceHistory(string $pair)
    {
        return Cache::remember("price_history_{$pair}", 5, function () use ($pair) {
            return PriceAggregate::query()
                ->where('pair', $pair)
                ->orderBy('timestamp', 'desc')
                ->limit(100)
                ->get();
        });
    }

    /**
     * Get supported pairs and exchanges.
     */
    public function getConfiguration()
    {
        return [
            'pairs' => config('crypto.pairs'),
            'exchanges' => config('crypto.exchanges'),
            'interval' => config('crypto.interval')
        ];
    }
}