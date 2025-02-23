<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;

Route::get('/', Dashboard::class);

Route::get('/test-crypto', function () {
    try {
        // Test configuration
        $config = [
            'pairs' => config('crypto.pairs'),
            'exchanges' => config('crypto.exchanges'),
            'interval' => config('crypto.interval'),
            'queue' => config('queue.default'),
        ];

        // Dispatch job
        \App\Jobs\FetchCryptoPrices::dispatch();

        return response()->json([
            'status' => 'success',
            'message' => 'Job dispatched',
            'config' => $config
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});