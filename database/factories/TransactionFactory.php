<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['credit', 'debit', 'withdrawal']);
        
        $amount = fake()->boolean(25) 
            ? fake()->randomFloat(2, 10001, 30000) 
            : fake()->randomFloat(2, 5, 9999);

        $needsApproval = $amount > 10000;
        
        $status = $needsApproval 
            ? fake()->randomElement(['pending_approval', 'completed', 'rejected']) 
            : fake()->randomElement(['pending', 'completed', 'failed']);

        $isApprovedOrRejected = in_array($status, ['completed', 'rejected']);

        $descriptions = [
            'credit' => ['Salary Deposit', 'Wire Transfer Inward', 'Cash Deposit ATM'],
            'debit' => ['Online Purchase', 'Merchant Payment', 'Bill Payment'],
            'withdrawal' => ['ATM Cash Withdrawal', 'Branch Teller Withdrawal'],
        ];

        return [
            'reference_number' => 'TXN-' . fake()->unique()->numberBetween(100000000, 999999999),
            'customer_id' => Customer::inRandomOrder()->first()?->id ?? Customer::factory(),
            'amount' => $amount,
            'type' => $type,
            'description' => fake()->randomElement($descriptions[$type]),
            'status' => $status,
            'needs_approval' => $needsApproval,
            
            'approved_by' => $isApprovedOrRejected && $needsApproval ? User::inRandomOrder()->first()?->id ?? User::factory() : null,
            'approved_at' => $isApprovedOrRejected && $needsApproval ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'rejection_reason' => $status === 'rejected' ? fake()->randomElement(['Unusual withdrawal pattern', 'Daily threshold limit breach']) : null,
            
            'transaction_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'transfer_id' => fake()->boolean(40) ? Transfer::inRandomOrder()->first()?->id : null,
        ];
    }
}