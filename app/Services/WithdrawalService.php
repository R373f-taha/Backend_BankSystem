<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Events\WithdrawalCompletedEvent;
use App\Models\Notification;;

class WithdrawalService
{
    private float $threshold;

    public function __construct()
    {
        $this->threshold = (float) config('bank.approval_threshold', 10000);
    }

    public function createWithdrawal($customer, array $data): array
    {
        $amount = $data['amount'];

        // Validate balance
        if ($customer->balance < $amount) {
            return [
                'success' => false,
                'message' => 'Insufficient balance.',
                'code'    => 422,
            ];
        }

        // Validate account status
        if ($customer->status !== 'active') {
            return [
                'success' => false,
                'message' => 'Account is not active.',
                'code'    => 422,
            ];
        }

        // Check approval threshold
        $needsApproval = $amount > $this->threshold;
        $status = $needsApproval ? 'pending_approval' : 'pending';

        $transaction = DB::transaction(function () use ($customer, $amount, $status, $needsApproval) {

            $transaction = Transaction::create([
                'reference_number'   => 'TXN-' . strtoupper(Str::random(10)),
                'customer_id'        => $customer->id,
                'amount'             => $amount,
                'type'   => 'withdrawal',
                'description'        => 'Cash Withdrawal',
                'status'             => $status,
                'needs_approval'     => $needsApproval,
                'approved_at' => $needsApproval ? now() : null,
            ]);

            // Only deduct balance if NOT needing approval
            if (!$needsApproval) {
                $customer->decrement('balance', $amount);
                $transaction->update(['status' => 'completed']);
            }

            return $transaction;
        });

        // Fire event only if completed immediately
        if (!$needsApproval) {
            event(new WithdrawalCompletedEvent($transaction, $customer));
        }

        $message = $needsApproval
            ? 'Withdrawal request submitted and pending employee approval.'
            : 'Withdrawal completed successfully.';

        return [
            'success' => true,
            'message' => $message,
            'data'    => $transaction->fresh()->load('customer.user'),
            'code'    => $needsApproval ? 202 : 201,
        ];
    }

    /**
     * Approve a pending withdrawal (called by employee)
     */
    public function approveWithdrawal(Transaction $transaction, int $employeeId): array
    {
        if ($transaction->status !== 'pending_approval') {
            return [
                'success' => false,
                'message' => 'This withdrawal does not require approval.',
                'code'    => 422,
            ];
        }

        if ($transaction->type !== 'withdrawal') {
            return [
                'success' => false,
                'message' => 'This is not a withdrawal transaction.',
                'code'    => 422,
            ];
        }

        return DB::transaction(function () use ($transaction, $employeeId) {
            $customer = $transaction->customer;

            // Double-check balance
            if ($customer->balance < $transaction->amount) {
                return [
                    'success' => false,
                    'message' => 'Customer has insufficient balance.',
                    'code'    => 422,
                ];
            }

            // Deduct balance
            $customer->decrement('balance', $transaction->amount);

            // Update transaction status
            $transaction->update([
                'status'      => 'completed',
                'approved_by' => $employeeId,
                'approved_at' => now(),
            ]);

            event(new WithdrawalCompletedEvent($transaction->fresh(), $customer));

            return [
                'success' => true,
                'message' => 'Withdrawal approved and completed successfully.',
                'data'    => $transaction->fresh()->load('customer.user'),
                'code'    => 200,
            ];
        });
    }

    /**
     * Reject a pending withdrawal (called by employee)
     */
    public function rejectWithdrawal(Transaction $transaction, int $employeeId, ?string $reason = null): array
    {
        if ($transaction->status !== 'pending_approval') {
            return [
                'success' => false,
                'message' => 'This withdrawal does not require approval.',
                'code'    => 422,
            ];
        }

        $transaction->update([
            'status'           => 'rejected',
            'approved_by'     => $employeeId,
            'approved_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        Notification::create([
            'user_id' => $transaction->customer->user_id,
            'title' => 'Withdrawal Rejected',
            'message' => "Your withdrawal of $" . $transaction->amount . " has been rejected. Reason: " . ($reason ?? 'No reason provided.'),
            'is_read' => false
        ]);

        return [
            'success' => true,
            'message' => 'Withdrawal rejected.',
            'data'    => $transaction->fresh(),
            'code'    => 200,
        ];
    }
}
