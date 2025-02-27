<?php

namespace App\Console\Commands;

use App\Jobs\FetchCryptoPrices;
use Illuminate\Console\Command;

class RunFetchCryptoPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-fetch-crypto-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the FetchCryptoPrices job';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching FetchCryptoPrices job...');
        FetchCryptoPrices::dispatch();
        $this->info('FetchCryptoPrices job dispatched successfully.');
    }
}
