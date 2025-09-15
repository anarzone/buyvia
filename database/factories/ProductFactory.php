<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $colors = ['red', 'blue', 'green', 'black', 'white', 'yellow', 'purple', 'pink'];
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

        return [
            'slug' => $this->faker->unique()->slug(),
            'title' => $this->faker->words(3, true),
            'brand' => $this->faker->optional(0.8)->company(),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'attributes' => [
                'color' => $this->faker->randomElement($colors),
                'size' => $this->faker->randomElement($sizes),
                'material' => $this->faker->optional(0.6)->randomElement(['cotton', 'polyester', 'wool', 'silk']),
                'weight' => $this->faker->optional()->numberBetween(100, 2000),
            ],
            'status' => $this->faker->randomElement(['draft', 'active', 'archived']),
        ];
    }
}
