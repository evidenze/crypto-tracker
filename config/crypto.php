<?php

return [
    'pairs' => explode(',', env('CRYPTO_PAIRS', 'BTCUSDC,BTCUSDT,BTCETH')),
    'exchanges' => explode(',', env('CRYPTO_EXCHANGES', 'binance,mexc,huobi')),
    'interval' => (int) env('PRICE_FETCH_INTERVAL', 5),
    'retry_attempts' => (int) env('PRICE_FETCH_RETRY_ATTEMPTS', 3),
    'api_token' => env('CRYPTO_API_TOKEN'),
];