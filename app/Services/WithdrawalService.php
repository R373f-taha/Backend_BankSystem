<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Events\WithdrawalCompletedEvent;
class WithdrawalService
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    public function createWithdrawal($customer, $data)
    {
        $amount = $data['amount'];

        if ($customer->balance < $amount) {

            return [
                'success' => false,
                'message' => 'Insufficient balance.',
                'code'    => 422
            ];
        }

        if ($customer->status !== 'active') {

            return [
                'success' => false,
                'message' => 'Account is not active.',
                'code'    => 422
            ];
        }

        $transaction = DB::transaction(function () use (
            $customer,
            $amount
        ) {

            $transaction = Transaction::create([
                'reference_number' =>
                'TXN-' . strtoupper(Str::random(10)),

                'customer_id' => $customer->id,

                'amount' => $amount,

                'type' => 'withdrawal',

                'description' => 'Cash Withdrawal',

                'status' => 'completed',
            ]);

            $customer->decrement(
                'balance',
                $amount
            );

            return $transaction;
        });
        event(
            new WithdrawalCompletedEvent(
                $transaction,
                $customer
            )
        );
        return [
            'success' => true,
            'message' => 'Withdrawal completed successfully.',
            'data'    => $transaction,
            'code'    => 201,
        ];
    }
}
