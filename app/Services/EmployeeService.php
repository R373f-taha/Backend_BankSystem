<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\AccountRequest;
use Illuminate\Support\Facades\DB;
use Exception;

class EmployeeService
{
    /**
     * Search customers by query, or email
     */
    public function searchCustomers(array $filters): Collection
    {
        return Customer::with(['user', 'accountRequest'])
            ->when(
                !empty($filters['identity_number']),
                fn($q) => $q->whereHas('accountRequest', fn($ar) => $ar->where('identity_number', $filters['identity_number']))
            )
            ->when(
                !empty($filters['name']),
                fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$filters['name']}%"))
            )
            ->when(
                !empty($filters['q']),
                function ($mainQuery) use ($filters) {
                    $searchTerm = "%{$filters['q']}%";
                    
                    $mainQuery->where(function ($subQuery) use ($searchTerm) {
                        $subQuery->whereHas('user', function ($u) use ($searchTerm) {
                            $u->where('name', 'like', $searchTerm)
                              ->orWhere('email', 'like', $searchTerm);
                        })
                        ->orWhereHas('accountRequest', function ($ar) use ($searchTerm) {
                            $ar->where('identity_number', 'like', $searchTerm);
                        });
                    });
                }
            )
            ->limit(20)
            ->get()
            ->map(fn($customer) => $this->formatCustomerSummary($customer));
    }

    /**
     * Get full customer details with transaction history
     */
    public function getCustomerDetails(Customer $customer): array
    {
        $customer->load([
            'user',
            'transactions' => fn($q) => $q->latest()->limit(50),
            'sentTransfer' => fn($q) => $q->with('receiver.user')->latest()->limit(20),
            'receivedTransfers' => fn($q) => $q->with('sender.user')->latest()->limit(20),
        ]);

        return [
            'profile' => $this->formatCustomerProfile($customer),
            'account' => [
                'id'           => $customer->id,
                'account_code' => $customer->account_code,
                'balance'      => $customer->balance,
                'status'       => $customer->status,
                'created_at'   => $customer->created_at,
            ],
            'recent_transactions' => $this->formatTransactions($customer->transactions),
            'transfer_sent'       => $this->formatTransfers($customer->sentTransfer, 'sender'),
            'transfer_received'   => $this->formatTransfers($customer->receivedTransfers, 'receiver'),
            'summary'             => $this->getCustomerSummary($customer),
        ];
    }

    /**
     * Get paginated transaction history for a customer
     */
    public function getCustomerTransactions(Customer $customer, int $limit = 50): Collection
    {
        return $customer->transactions()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($t) => $this->formatTransaction($t));
    }

    /**
     * Get paginated transfer history for a customer
     */
    public function getCustomerTransfers(Customer $customer, int $limit = 50): array
    {
        return [
            'sent'      => $customer->sentTransfer()
                ->with('receiver.user')
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn($t) => $this->formatTransfer($t, 'sender')),
            'received'  => $customer->receivedTransfers()
                ->with('sender.user')
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn($t) => $this->formatTransfer($t, 'receiver')),
        ];
    }

    // ==================== Private Formatters ====================

    private function formatCustomerSummary(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->user?->name,
            'email' => $customer->user?->email,
            'identity_number' => $customer->accountRequest?->identity_number,
            'account_code' => $customer->account_code,
            'status' => $customer->status,
            'balance' => $customer->balance,
        ];
    }

    private function formatCustomerProfile(Customer $customer): array
    {
        return [
            'id'         => $customer->id,
            'name'       => $customer->user?->name,
            'email'      => $customer->user?->email,
            'created_at' => $customer->user?->created_at,
        ];
    }

    private function formatTransactions(Collection $transactions): array
    {
        return $transactions->map(fn($t) => $this->formatTransaction($t))->toArray();
    }

    private function formatTransaction($transaction): array
    {
        return [
            'id'            => $transaction->id,
            'reference'     => $transaction->reference_number,
            'type'          => $transaction->type,
            'amount'        => $transaction->amount,
            'description'   => $transaction->description,
            'status'        => $transaction->status,
            'date'          => $transaction->created_at?->toDateTimeString(),
        ];
    }

    private function formatTransfers(Collection $transfers, string $direction): array
    {
        return $transfers->map(fn($t) => $this->formatTransfer($t, $direction))->toArray();
    }

    private function formatTransfer($transfer, string $direction): array
    {
        $otherParty = $direction === 'sender'
            ? $transfer->receiver
            : $transfer->sender;

        return [
            'id'          => $transfer->id,
            'reference'   => $transfer->reference_number,
            'amount'      => $transfer->amount,
            'status'      => $transfer->status,
            'other_party' => [
                'id'    => $otherParty?->id,
                'name'  => $otherParty?->user?->name,
                'email' => $otherParty?->user?->email,
            ],
            'notes'       => $transfer->notes,
            'date'        => $transfer->created_at?->toDateTimeString(),
        ];
    }

    private function getCustomerSummary(Customer $customer): array
    {
        $totalTransfers = $customer->sentTransfer()->count() + $customer->receivedTransfers()->count();
        $totalTransactions = $customer->transactions()->count();
        $pendingApprovals = $customer->transactions()->where('status', 'pending_approval')->count() +
            $customer->sentTransfer()->where('status', 'pending_approval')->count();

        return [
            'total_transfers'      => $totalTransfers,
            'total_transactions'   => $totalTransactions,
            'pending_approvals'    => $pendingApprovals,
        ];
    }

    public function freezeAccount(Customer $customer, int $employeeId, string $reason): array
    {
        if ($customer->status === 'frozen') {
            return ['success' => false, 'message' => 'Account is already frozen'];
        }

        $customer->update([
            'status' => 'frozen',
            'frozen_at'       => now(),
            'frozen_reason'   => $reason,
            'unfrozen_at'     => null,
            'unfrozen_reason' => null,
        ]);

        return ['success' => true, 'message' => 'Account frozen successfully'];
    }

    public function unfreezeAccount(Customer $customer, int $employeeId, string $reason): array
    {
        if ($customer->status !== 'frozen') {
            return ['success' => false, 'message' => 'Only frozen accounts can be unfrozen'];
        }

        $customer->update([
            'status' => 'active',
            'unfrozen_at'    => now(),
            'unfrozen_reason' => $reason,
        ]);

        return ['success' => true, 'message' => 'Account unfrozen successfully'];
    }

    public function createCustomer(array $data): array
    {
        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'A user with this email already exists.',
            ];
        }

        $existingIdentity = AccountRequest::where('identity_number', $data['identity_number'])->first();
        if ($existingIdentity) {
            return [
                'success' => false,
                'message' => 'A request with this identity number already exists.',
            ];
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name'     => $data['full_name'] ?? $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password'] ?? 'password123'),
                'role'     => 'customer',
            ]);

            $accountRequest = AccountRequest::create([
                'full_name'         => $data['full_name'] ?? $data['name'],
                'date_of_birth'     => $data['date_of_birth'],
                'gender'            => $data['gender'] ?? 'male',
                'marital_status'    => $data['marital_status'] ?? 'single',
                'identity_number'   => $data['identity_number'],
                'address'           => $data['address'] ?? '',
                'occupation'        => $data['occupation'] ?? 'Employee',
                'deposit_amount'    => $data['balance'] ?? 0,
                'status'            => 'accepted',         
                'email'             => $data['email'],
                'verified_at'       => now(),
                'admin_id'          => auth()->id(),        
            ]);

            do {
                $accountCode = 'AC-' . mt_rand(10000000, 99999999);
            } while (Customer::where('account_code', $accountCode)->exists());

            $customer = Customer::create([
                'user_id'      => $user->id,
                'email'        => $user->email, 
                'account_code' => $accountCode,
                'balance'      => $data['balance'] ?? 0,
                'status'       => 'active',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'User, Account Request, and Customer created successfully.',
                'data'    => [
                    'user'            => $user,
                    'account_request' => $accountRequest,
                    'customer'        => $customer,
                ],
            ];

        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Failed to create customer: ' . $e->getMessage(),
            ];
        }
    }
}