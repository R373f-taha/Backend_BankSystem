<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
      
        $descriptions = [
            'credit' => [
                'Salary Transfer',
                'ATM Cash Deposit',
                'Inward Bank Transfer',
                'Refund from Merchant',
                'Interest Credited'
            ],
            'debit' => [
                'Online Shopping Payment',
                'ATM Cash Withdrawal',
                'Electricity Bill Payment',
                'Restaurant Dining',
                'Monthly Account Fees'
            ]
        ];

      
        $type = fake()->randomElement(['credit', 'debit']);
        
    
        $description = fake()->randomElement($descriptions[$type]);

        return [
            // جلب ID عميل عشوائي موجود مسبقاً في الداتابيز
            'customer_id' => Customer::inRandomOrder()->first()?->id ?? Customer::factory(),
            'amount' => fake()->randomFloat(2, 10, 5000), // مبالغ تتراوح بين 10 و 5000 دولار مع كسرين عشريين
            'type' => $type,
            'description' => $description,
            
            'reference_number' => 'TXN-' . fake()->unique()->numberBetween(100000000, 999999999),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected', 'completed']),
            'transaction_date' => fake()->dateTimeBetween('-2 months', 'now'), // عمليات بتاريخ آخر شهرين
        ];
    }
}