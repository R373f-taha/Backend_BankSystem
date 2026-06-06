<?php

namespace Database\Factories;

use App\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // ميزات بنكية منوعة ومجهزة حسب التصنيف مع أيقوناتها
        $featuresData = [
            'online_booking' => [
                ['title' => 'Instant Account Opening', 'desc' => 'Open your bank account digitally in less than 5 minutes without visiting a branch.', 'icon' => 'user-plus'],
                ['title' => '24/7 Digital Banking', 'desc' => 'Access your funds, transfer money, and pay bills anytime, anywhere securely.', 'icon' => 'laptop-bank'],
            ],
            'loan' => [
                ['title' => 'Flexible Home Loans', 'desc' => 'Get competitive interest rates on housing loans with flexible repayment plans up to 30 years.', 'icon' => 'home'],
                ['title' => 'Express Personal Loans', 'desc' => 'Quick approvals on personal loans with minimal documentation to fund your urgent needs.', 'icon' => 'hand-holding-usd'],
            ],
            'investment' => [
                ['title' => 'Smart Fixed Deposits', 'desc' => 'Grow your wealth with high-yield fixed deposits and flexible tenure choices.', 'icon' => 'chart-line'],
                ['title' => 'Mutual Funds Portfolio', 'desc' => 'Invest in curated mutual funds managed by financial experts to secure your future.', 'icon' => 'briefcase'],
            ]
        ];

        // اختيار تصنيف عشوائي
        $category = fake()->randomElement(['online_booking', 'loan', 'investment']);
        
        // اختيار ميزة متناسقة مع التصنيف
        $feature = fake()->randomElement($featuresData[$category]);

        static $order = 1;

        return [
            'title' => $feature['title'],
            'description' => $feature['desc'],
            'icon_name' => $feature['icon'],
            'category' => $category,
            'display_order' => $order++,
            'is_active' => true,
        ];
    }
}