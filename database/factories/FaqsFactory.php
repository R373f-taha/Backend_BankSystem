<?php

namespace Database\Factories;

use App\Models\Faqs;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqsFactory extends Factory
{
    protected $model = Faqs::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // لستة بأسئلة وأجوبة بنكية واقعية
        $faqsData = [
            [
                'q' => 'How do I open a new bank account online?',
                'a' => 'You can open a new account entirely through our mobile app or website by providing your national ID and completing the digital verification process.'
            ],
            [
                'q' => 'What should I do if my credit card is lost or stolen?',
                'a' => 'Lock your card immediately via our mobile app, or call our 24/7 customer support line to block it and request a replacement.'
            ],
            [
                'q' => 'Are there any monthly fees for the basic savings account?',
                'a' => 'No, our basic savings account requires no monthly maintenance fees as long as you maintain the minimum balance requirements.'
            ],
            [
                'q' => 'How long does an international money transfer take?',
                'a' => 'International transfers typically take between 1 to 3 business days, depending on the destination country and intermediary banks.'
            ],
            [
                'q' => 'How can I apply for a personal or car loan?',
                'a' => 'You can apply directly through the "Loans" section in your online banking dashboard, or visit any of our branches with your income proof.'
            ]
        ];

        // اختيار سؤال وجواب بشكل تسلسلي أو عشوائي
        $faq = fake()->unique()->randomElement($faqsData) ?? [
            'q' => 'Default Bank Question?',
            'a' => 'Default bank answer for our clients.'
        ];

        static $order = 1; 

        return [
            'page_id' => null,
            'question' => $faq['q'],
            'answer' => $faq['a'],
            'display_order' => $order++,
            'is_active' => true, 
        ];
    }
}