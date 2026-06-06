<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), 
            
            'email' => $this->faker->unique()->safeEmail(),
            
            'status' => $this->faker->randomElement(['active', 'un_active']),
            
            'account_code' => 'AC-' . $this->faker->unique()->numberBetween(10000000, 99999999),
            
            'balance' => $this->faker->randomFloat(2, 0, 5000),
        ];
    }
}