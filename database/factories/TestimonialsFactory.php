<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Testimonials;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestimonialsFactory extends Factory
{
    protected $model = Testimonials::class;

    public function definition(): array
    {
        $texts = [
            'individual' => [
                'The mobile banking app is incredibly fast and secure. Best banking experience ever!',
                'Customer service resolved my card issue within minutes. Highly recommended!',
                'Opening a savings account was smooth and digital. No paperwork needed.',
                'Very low transaction fees compared to other banks. Love it!',
            ],
            'business' => [
                'Our company corporate payroll handling has become seamless with this banking platform.',
                'Excellent business loan terms and outstanding support for startups.',
                'The multi-currency account features saved us thousands in foreign exchange fees.',
                'Highly professional banking services. Their merchant tools are top-notch.',
            ]
        ];

        $type = fake()->randomElement(['individual', 'business']);
        $testimonialText = fake()->randomElement($texts[$type]);

        return [
            'customer_id' => Customer::inRandomOrder()->first()?->id ?? Customer::factory(),
            'testimonial_text' => $testimonialText,
            'rating' => fake()->numberBetween(4, 5),
            'type' => $type,
            'is_approved' => fake()->boolean(80),
           
            'created_at' => now(), 
        ];
    }
}