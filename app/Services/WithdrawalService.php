<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Events\WithdrawalCompletedEvent;
use App\Models\Notification;

class WithdrawalService
{
    private float $threshold;

    public function __construct()
    {
        $this->threshold = (float) config('bank.approval_threshold', 10000);
    }

    /**
     * Initialize a new withdrawal request.
     * Funds are not deducted yet; transaction waits for admin approval or cash pick-up.
     */
    public function createWithdrawal($customer, array $data): array
    {
        $amount = $data['amount'];
        $companyId = $data['transfer_company_id'];

        // Validate customer balance
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

        // Determine if the withdrawal amount exceeds the immediate processing threshold
        $needsApproval = $amount > $this->threshold;
        
        // If approval is needed, route to 'pending_approval'; otherwise, set to 'pending' awaiting user verification at the branch
        $status = $needsApproval ? 'pending_approval' : 'pending'; 

        $transaction = DB::transaction(function () use ($customer, $amount, $status, $needsApproval, $companyId) {
            return Transaction::create([
                'reference_number'    => 'TXN-' . strtoupper(Str::random(10)),
                'customer_id'         => $customer->id,
                'transfer_company_id' => $companyId, // Store the selected transfer vendor (e.g., Al-Haram, Al-Fouad)
                'amount'              => $amount,
                'type'                => 'withdrawal',
                'description'         => 'Cash Withdrawal via Transfer Company',
                'status'              => $status,
                'needs_approval'      => $needsApproval,
                'approved_at'         => null, // Approval timestamp is null initially as funds are not yet handed over
            ]);
        });

        // Dispatch an internal system notification containing the reference code for the vendor branch
        Notification::create([
            'user_id' => $customer->user_id,
            'title'   => 'New Withdrawal Request',
            'message' => "Your withdrawal request for {$amount} has been initialized. Please head to the selected transfer office and present this reference code: {$transaction->reference_number}",
            'is_read' => false
        ]);

        $message = $needsApproval
            ? 'Withdrawal request submitted and pending employee approval.'
            : 'Withdrawal request created. Please confirm upon cash receipt.';

        return [
            'success' => true,
            'message' => $message,
            'data'    => $transaction->fresh()->load('customer.user', 'transferCompany'),
            'code'    => $needsApproval ? 202 : 201,
        ];
    }

    /**
     * Approve a pending withdrawal (Called by system managers/employees when exceeding threshold).
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

        // Move transaction state to 'pending' so it becomes ready for user cash collection at the counter
        $transaction->update([
            'status'      => 'pending', 
            'approved_by' => $employeeId,
            'approved_at' => now(),
        ]);

        Notification::create([
            'user_id' => $transaction->customer->user_id,
            'title'   => 'Withdrawal Request Approved',
            'message' => "Management approved your withdrawal request of {$transaction->amount}. You can now claim your cash using reference code: {$transaction->reference_number}",
            'is_read' => false
        ]);

        return [
            'success' => true,
            'message' => 'Withdrawal approved by admin. Pending customer confirmation at company.',
            'data'    => $transaction->fresh()->load('customer.user'),
            'code'    => 200,
        ];
    }

    /**
     * Reject a pending withdrawal request (Called by system managers/employees).
     */
    public function rejectWithdrawal(Transaction $transaction, int $employeeId, ?string $reason = null): array
    {
        if ($transaction->status !== 'pending_approval') {
            return [
                'success' => false,
                'message' => 'This withdrawal cannot be rejected or doesn\'t require approval.',
                'code'    => 422,
            ];
        }

        $transaction->update([
            'status'           => 'rejected',
            'approved_by'      => $employeeId,
            'approved_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        Notification::create([
            'user_id' => $transaction->customer->user_id,
            'title'   => 'Withdrawal Rejected',
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

    /**
     * Confirm that the user has successfully received the cash from the branch.
     * This method executes the actual database balance deductions.
     */
    public function confirmWithdrawal($customer, $withdrawalId): array
    {
        $transaction = Transaction::where('id', $withdrawalId)
            ->where('customer_id', $customer->id)
            ->where('type', 'withdrawal')
            ->first();

        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Withdrawal transaction not found.',
                'code'    => 404,
            ];
        }

        // Confirmation can only happen if the status is active and waiting for collection ('pending')
        if ($transaction->status !== 'pending') {
            return [
                'success' => false,
                'message' => 'This withdrawal cannot be confirmed. Current status: ' . $transaction->status,
                'code'    => 422,
            ];
        }

        // Double check balance right before executing atomic database changes
        if ($customer->balance < $transaction->amount) {
            return [
                'success' => false,
                'message' => 'Insufficient balance to complete confirmation.',
                'code'    => 422,
            ];
        }

        // Atomically deduct user balance and set final successful status
        DB::transaction(function () use ($customer, $transaction) {
            $customer->decrement('balance', $transaction->amount);
            $transaction->update(['status' => 'confirmed']);
        });

        event(new WithdrawalCompletedEvent($transaction->fresh(), $customer));

        return [
            'success' => true,
            'message' => 'Withdrawal confirmed and funds deducted successfully.',
            'data'    => $transaction->fresh()->load('customer.user', 'transferCompany'),
            'code'    => 200,
        ];
    }
}