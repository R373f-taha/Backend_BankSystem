<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class TransactionService
{

    public function deposit(Customer $customer, float $amount, ?string $description = null)
    {
        return DB::transaction(function () use ($customer, $amount, $description) {


            $customer->balance += $amount;
            $customer->save();

            $transaction = Transaction::create([
                'customer_id' => $customer->id,
                'amount' => $amount,
                'type' => 'credit',
                'status' => 'completed',
                'description' => $description ?? 'Deposit to account',
                'reference_number' => 'DEP-' . strtoupper(uniqid())
            ]);

            $userId = Auth::id();
            $title = "Successful Deposit 💰";
            $message = "An amount of " . number_format($amount, 2) . " has been successfully deposited into your account. Your current balance is: " . number_format($customer->balance, 2);

            NotificationService::add($userId, $title, $message);
            return $transaction;
        });
    }
    public function getBalance(Customer $customer): float
    {
        return (float) $customer->balance;
    }

    public function getAccountStatement(Customer $customer, array $filters = [])
    {
        $query = $customer->transactions();
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('transaction_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('transaction_date', '<=', $filters['to_date']);
        }
        return $query->orderBy('transaction_date', 'desc')->get();
    }
    public function getLatestTransactions($customerId)
    {
        return Transaction::where('customer_id', $customerId)
            ->with(['transfer.sender', 'transfer.receiver'])
            ->latest()
            ->take(6)
            ->get();
    }
}