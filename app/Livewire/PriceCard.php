<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\On;

class PriceCard extends Component
{
    public $pair;
    public $price;
    public $previousPrice;
    public $changePercentage;
    public $highest;
    public $lowest;
    public $timestamp;
    public $exchanges;
    public $isHighlighted = false;

    public function mount($data = null)
    {
        if ($data) {
            $this->pair = $data['pair'];
            $this->price = $data['price'];
            $this->previousPrice = $data['price'];
            $this->changePercentage = $data['change_percentage'];
            $this->highest = $data['highest'];
            $this->lowest = $data['lowest'];
            $this->timestamp = $data['timestamp'];
            $this->exchanges = $data['exchanges'];
        }
    }

    #[On('price-updated')]
    public function handlePriceUpdate($data)
    {

        if ($data['pair'] === $this->pair) {
            $this->previousPrice = $this->price;
            $this->price = $data['price'];
            $this->changePercentage = $data['change_percentage'];
            $this->highest = $data['highest'];
            $this->lowest = $data['lowest'];
            $this->timestamp = $data['timestamp'];
            $this->exchanges = $data['exchanges'];
            
            $this->isHighlighted = true;
            
            // Reset highlight after animation using dispatch with delay
            $this->dispatch('reset-highlight', delay: 1000);
        }
    }

    #[On('reset-highlight')]
    public function resetHighlight()
    {
        $this->isHighlighted = false;
    }

    public function render()
    {
        return view('livewire.price-card');
    }
}