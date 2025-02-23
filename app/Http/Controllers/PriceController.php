<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceAggregate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PriceController extends Controller
{
    /**
     * Get latest prices for all configured pairs
     */
    public function latest(): JsonResponse
    {
        $prices = Cache::remember('latest_prices', 5, function () {
            return PriceAggregate::query()
                ->select(['pair', 'price', 'change_percentage', 'highest', 'lowest', 'timestamp', 'exchanges'])
                ->orderBy('timestamp', 'desc')
                ->get()
                ->unique('pair')
                ->values();
        });

        return response()->json([
            'status' => 'success',
            'data' => $prices
        ]);
    }

    /**
     * Get historical prices for a specific pair
     */
    public function history(string $pair): JsonResponse
    {
        $history = Cache::remember("price_history_{$pair}", 5, function () use ($pair) {
            return PriceAggregate::query()
                ->where('pair', $pair)
                ->orderBy('timestamp', 'desc')
                ->limit(100)
                ->get();
        });

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }

    /**
     * Get supported pairs and exchanges
     */
    public function configuration(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'pairs' => config('crypto.pairs'),
                'exchanges' => config('crypto.exchanges'),
                'interval' => config('crypto.interval')
            ]
        ]);
    }
}