<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransferFactory extends Factory
{
    protected $model = Transfer::class;

    public function definition(): array
    {
        $amount = fake()->boolean(30)
            ? fake()->randomFloat(2, 10001, 50000)
            : fake()->randomFloat(2, 10, 9999);

        $needsApproval = $amount > 10000;

        $status = $needsApproval
            ? fake()->randomElement(['pending_approval', 'completed', 'rejected'])
            : fake()->randomElement(['pending', 'completed', 'failed']);

        $isApprovedOrRejected = in_array($status, ['completed', 'rejected']);

        $senderId = Customer::inRandomOrder()->first()?->id ?? Customer::factory();

        $receiverId = Customer::where('id', '!=', $senderId)->inRandomOrder()->first()?->id ?? Customer::factory();

        return [
            'reference_number' => 'TRF-' . fake()->unique()->numberBetween(100000000, 999999999),

            'sender_id' => $senderId,
            'receiver_id' => $receiverId,

            'amount' => $amount,
            'notes' => fake()->sentence(),
            'status' => $status,
            'needs_approval' => $needsApproval,

            'approved_by' => $isApprovedOrRejected && $needsApproval ? User::inRandomOrder()->first()?->id ?? User::factory() : null,
            'approved_at' => $isApprovedOrRejected && $needsApproval ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'rejection_reason' => $status === 'rejected' ? fake()->randomElement(['Suspicious activity', 'High risk transaction', 'Exceeded daily limit']) : null,
            'completed_at' => $status === 'completed' ? fake()->dateTimeBetween('-1 month', 'now') : null,
        ];
    }
}
