<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;


    public function definition(): array
    {
        
        $products = [
            ['title' => 'Savings Account Plus', 'icon' => 'wallet', 'type' => 'individual'],
            ['title' => 'Premium Credit Card', 'icon' => 'credit-card', 'type' => 'individual'],
            ['title' => 'Personal Loan', 'icon' => 'bank', 'type' => 'individual'],
            ['title' => 'Home Mortgage', 'icon' => 'home', 'type' => 'individual'],
            ['title' => 'Corporate Payroll Solution', 'icon' => 'briefcase', 'type' => 'business'],
            ['title' => 'Business Growth Loan', 'icon' => 'trending-up', 'type' => 'business'],
            ['title' => 'Merchant Account Services', 'icon' => 'shield-check', 'type' => 'business'],
        ];

        
        $selected = fake()->randomElement($products);

        return [
            'title' => $selected['title'],
            'description' => fake()->sentence(12), 
            'icon_name' => $selected['icon'],
            'product_type' => $selected['type'],
            'is_active' => true,
            'display_order' => fake()->numberBetween(1, 10),
        ];
    }
}