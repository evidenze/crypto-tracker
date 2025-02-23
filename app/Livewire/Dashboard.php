<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PriceAggregate;

class Dashboard extends Component
{
    public function render()
    {
        $prices = PriceAggregate::query()
            ->select(['pair', 'price', 'change_percentage', 'highest', 'lowest', 'timestamp', 'exchanges'])
            ->orderBy('timestamp', 'desc')
            ->get()
            ->unique('pair')
            ->values();

        return view('livewire.dashboard', [
            'prices' => $prices
        ]);
    }
}