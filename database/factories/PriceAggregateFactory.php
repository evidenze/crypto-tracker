<?php

namespace Database\Factories;

use App\Models\PriceAggregate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PriceAggregate>
 */
class PriceAggregateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PriceAggregate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'pair' => $this->faker->currencyCode . '/' . $this->faker->currencyCode,
            'price' => $this->faker->randomFloat(2, 1, 10000),
            'change_percentage' => $this->faker->randomFloat(2, -10, 10),
            'highest' => $this->faker->randomFloat(2, 100, 10000),
            'lowest' => $this->faker->randomFloat(2, 1, 100),
            'timestamp' => $this->faker->dateTimeThisYear(),
            'exchanges' => json_encode(['Binance', 'Coinbase']),
        ];
    }
}
