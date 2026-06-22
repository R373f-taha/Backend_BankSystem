<?php

namespace App\Services;

use App\Events\TransferCompletedEvent;
use App\Models\AccountRequest;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Notification;

class TransferService
{
    private float $threshold;

    public function __construct()
    {
        $this->threshold = (float) config('bank.approval_threshold', 10000);
    }

    public function executeTransfer(Customer $sender, Customer $receiver, float $amount, ?string $notes = null): array
    {
        // Validate balance
        if ($sender->balance < $amount) {
            return [
                'success' => false,
                'message' => 'Insufficient balance. You don\'t have enough funds to complete this transfer 😒😒',
                'code'    => 422,
            ];
        }

        // Prevent self-transfer
        if ($sender->user_id === $receiver->user_id) {
            return [
                'success' => false,
                'message' => 'You cannot transfer money to your own account 😐❌',
                'code'    => 422,
            ];
        }

        // Validate account status
        if ($sender->status !== 'active' || $receiver->status !== 'active') {
            return [
                'success' => false,
                'message' => 'Both accounts must be active to complete the transfer 😑❌',
                'code'    => 422,
            ];
        }

        // Check approval threshold
        $needsApproval = $amount > $this->threshold;
        $status = $needsApproval ? 'pending_approval' : 'pending';

        $transfer = DB::transaction(function () use ($sender, $receiver, $amount, $notes, $status, $needsApproval) {

            $transfer = Transfer::create([
                'reference_number'      => 'TRF-' . strtoupper(Str::random(10)),
                'sender_id'             => $sender->id,
                'receiver_id'           => $receiver->id,
                'amount'                => $amount,
                'notes'                 => $notes,
                'status'                => $status,
                'needs_approval'        => $needsApproval,
                'approval_requested_at' => $needsApproval ? now() : null,
            ]);

            // Create debit transaction (always created, but status depends on transfer)
            $debitTx = Transaction::create([
                'reference_number' => 'TXN-' . strtoupper(Str::random(10)),
                'customer_id'     => $sender->id,
                'amount'          => $amount,
                'type' => 'debit',
                'description'     => "Transfer to {$receiver->user->name} ({$receiver->user->email})",
                'status'          => $status,
                'transfer_id'     => $transfer->id,
                'needs_approval'  => $needsApproval,
                'approved_at' => $needsApproval ? now() : null,
            ]);

            // Only deduct balance if NOT needing approval
            if (!$needsApproval) {
                $sender->decrement('balance', $amount);

                // Create credit transaction
                Transaction::create([
                    'reference_number'  => 'TXN-' . strtoupper(Str::random(10)),
                    'customer_id'       => $receiver->id,
                    'amount'            => $amount,
                    'type'  => 'credit',
                    'description'       => "Transfer received from {$sender->user->name} ({$sender->user->email})",
                    'status'            => 'completed',
                    'transfer_id'       => $transfer->id,
                ]);

                $receiver->increment('balance', $amount);

                $transfer->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);

                $debitTx->update(['status' => 'completed']);
            }

            return $transfer;
        });

        // Fire event only if completed immediately
        if (!$needsApproval) {
            event(new TransferCompletedEvent($transfer));
        }

        $message = $needsApproval
            ? 'Transfer request submitted and pending employee approval.'
            : 'Transfer completed successfully.';

        return [
            'success' => true,
            'message' => $message,
            'data'    => $transfer->fresh()->load(['sender.user', 'receiver.user']),
            'code'    => $needsApproval ? 202 : 201,
        ];
    }

    /**
     * Approve a pending transfer (called by employee)
     */
    public function approveTransfer(Transfer $transfer, int $employeeId): array
    {
        if ($transfer->status !== 'pending_approval') {
            return [
                'success' => false,
                'message' => 'This transfer does not require approval.',
                'code'    => 422,
            ];
        }

        return DB::transaction(function () use ($transfer, $employeeId) {
            $sender = $transfer->sender;
            $receiver = $transfer->receiver;

            // Double-check balance
            if ($sender->balance < $transfer->amount) {
                return [
                    'success' => false,
                    'message' => 'Sender has insufficient balance.',
                    'code'    => 422,
                ];
            }

            // Deduct from sender
            $sender->decrement('balance', $transfer->amount);

            // Credit to receiver
            $receiver->increment('balance', $transfer->amount);

            // Update transfer status
            $transfer->update([
                'status'        => 'completed',
                'completed_at'  => now(),
                'approved_by'   => $employeeId,
                'approved_at'   => now(),
            ]);

            // Update associated debit transaction
            $transfer->transactions()
                ->where('type', 'debit')
                ->update(['status' => 'completed']);

            // Create credit transaction for receiver
            Transaction::create([
                'reference_number'  => 'TXN-' . strtoupper(Str::random(10)),
                'customer_id'       => $receiver->id,
                'amount'            => $transfer->amount,
                'type'  => 'credit',
                'description'       => "Transfer received from {$sender->user->name} ({$sender->user->email})",
                'status'            => 'completed',
                'transfer_id'       => $transfer->id,
            ]);

            event(new TransferCompletedEvent($transfer->fresh()));

            return [
                'success' => true,
                'message' => 'Transfer approved and completed successfully.',
                'data'    => $transfer->fresh()->load(['sender.user', 'receiver.user']),
                'code'    => 200,
            ];
        });
    }

    /**
     * Reject a pending transfer (called by employee)
     */
    public function rejectTransfer(Transfer $transfer, int $employeeId, ?string $reason = null): array
    {
        if ($transfer->status !== 'pending_approval') {
            return [
                'success' => false,
                'message' => 'This transfer does not require approval.',
                'code'    => 422,
            ];
        }

        $transfer->update([
            'status'          => 'rejected',
            'approved_by'     => $employeeId,
            'rejection_reason' => $reason,
        ]);

        // Update associated transactions
        $transfer->transactions()
            ->where('type', 'debit')
            ->update(['status' => 'rejected']);

         Notification::create([
          'user_id'=>$transfer->sender_id,
           'title'=>'Transfer Rejected',
           'message' => "Your transfer of $" . $transfer->amount. " to has been rejected.",
            'is_read' => false
        ]);

        return [
            'success' => true,
            'message' => 'Transfer rejected.',
            'data'    => $transfer->fresh(),
            'code'    => 200,
        ];
    }
}
