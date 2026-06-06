<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function deposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $customer = $user->customer;

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer profile not found for this user.'
            ], 404);
        }
        $transaction = $this->transactionService->deposit($customer, $request->amount, $request->description);
        return response()->json([
            'status' => 'success',
            'message' => 'Money deposited successfully',
            'current_balance' => $customer->balance,
            'transaction' => $transaction
        ], 200);
    }
    public function showBalance()
    {
        $user = auth()->user();
        $customer = $user->customer;

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer profile not found for this user.'
            ], 404);
        }
        $balance = $this->transactionService->getBalance($customer);

        return response()->json([
            'status' => 'success',
            'account_code' => $customer->account_code,
            'current_balance' => $balance
        ], 200);
    }
    public function accountStatement(Request $request)
    {
        $user = auth()->user();
        $customer = $user->customer;

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer profile not found for this user.'
            ], 404);
        }

        $filters = [
            'type' => $request->query('type'),
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
        ];

        $transactions = $this->transactionService->getAccountStatement($customer, $filters);

        return response()->json([
            'status' => 'success',
            'count' => $transactions->count(),
            'transactions' => $transactions
        ], 200);
    }
}