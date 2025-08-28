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
        return [
            'slug' => $this->faker->slug(),
            'title' => $this->faker->word(),
            'brand' => $this->faker->word(),
            'description' => $this->faker->text(),
            'attributes' => $this->faker->words(),
            'status' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
