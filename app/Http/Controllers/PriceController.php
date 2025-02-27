<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PriceService;
use Illuminate\Http\JsonResponse;

class PriceController extends Controller
{
    protected $priceService;

    /**
     * Inject the PriceService dependency.
     */
    public function __construct(PriceService $priceService)
    {
        $this->priceService = $priceService;
    }

    /**
     * Get the latest prices for all configured pairs.
     */
    public function latest(): JsonResponse
    {
        $prices = $this->priceService->getLatestPrices();

        return response()->json([
            'status' => 'success',
            'data' => $prices
        ]);
    }

    /**
     * Get historical prices for a specific pair.
     */
    public function history(string $pair): JsonResponse
    {
        $history = $this->priceService->getPriceHistory($pair);

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }

    /**
     * Get supported pairs and exchanges.
     */
    public function configuration(): JsonResponse
    {
        $config = $this->priceService->getConfiguration();

        return response()->json([
            'status' => 'success',
            'data' => $config
        ]);
    }
}