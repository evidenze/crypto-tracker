<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">Crypto Price Tracker</h1>
    
    <livewire:digital-clock />
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        @foreach($prices as $price)
            <livewire:price-card 
                :wire:key="'price-'.$price->pair"
                :data="$price" 
            />
        @endforeach
    </div>
</div>