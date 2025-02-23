<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class DigitalClock extends Component
{
    public $time;

    public $timezone;

    public function mount()
    {
        $this->timezone = config('app.timezone');
        $this->time = now()->setTimezone($this->timezone);
    }

    public function setTimezone($tz)
    {
        $this->timezone = $tz;
        $this->time = now()->setTimezone($this->timezone);
    }

    #[On('refresh-time')]
    public function refreshTime()
    {
        $this->time = now()->setTimezone($this->timezone);
    }

    public function render()
    {
        return view('livewire.digital-clock');
    }
}