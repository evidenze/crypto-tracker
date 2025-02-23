<div class="bg-white rounded-lg shadow-md p-6 relative transition-all duration-300"
     x-data="{ show: false }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-90"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-90"
     x-init="show = true"
     wire:key="price-card-{{ $pair }}"
     @class([
        'border-l-4 border-green-500' => $changePercentage > 0,
        'border-l-4 border-red-500' => $changePercentage < 0,
     ])>
    
    {{-- Highlight Overlay --}}
    <div class="absolute inset-0 bg-yellow-100 opacity-0 transition-opacity duration-500 rounded-lg"
         :class="{ 'opacity-25': $wire.isHighlighted }">
    </div>
    
    {{-- Content --}}
    <div class="relative z-10">
        {{-- Header with Pair and Direction Icon --}}
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">{{ $pair }}</h2>
            <div class="flex items-center space-x-2">
                @if($changePercentage > 0)
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                @else
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                @endif
            </div>
        </div>
        
        {{-- Price Display --}}
        <div class="text-3xl font-bold mb-4" 
             @class([
                'text-green-600' => $changePercentage > 0,
                'text-red-600' => $changePercentage < 0
             ])>
            ${{ number_format($price, 2) }}
        </div>
        
        {{-- Change Percentage --}}
        <div class="text-sm mb-4">
            <span @class([
                'text-green-600' => $changePercentage > 0,
                'text-red-600' => $changePercentage < 0
            ])>
                {{ $changePercentage > 0 ? '+' : '' }}{{ number_format($changePercentage, 2) }}%
            </span>
            <span class="text-gray-500 ml-2">24h Change</span>
        </div>
        
        {{-- High/Low Values --}}
        <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
            <div>
                <div class="text-gray-500">24h High</div>
                <div class="font-semibold">${{ number_format($highest, 2) }}</div>
            </div>
            <div>
                <div class="text-gray-500">24h Low</div>
                <div class="font-semibold">${{ number_format($lowest, 2) }}</div>
            </div>
        </div>

        {{-- Exchanges --}}
        <div class="mb-4 text-sm">
            <span class="text-gray-500">Exchanges:</span>
            <div class="flex flex-wrap gap-2 mt-1">
                @foreach($exchanges as $exchange)
                    <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">
                        {{ ucfirst($exchange) }}
                    </span>
                @endforeach
            </div>
        </div>
        
        {{-- Timestamp --}}
        <div class="text-xs text-gray-500">
            Last Updated: {{ \Carbon\Carbon::parse($timestamp)->diffForHumans() }}
        </div>
    </div>
</div>