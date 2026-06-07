<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\Customer;
use App\Services\TransferService;
use App\Services\WithdrawalService;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateCustomerByEmployeeRequest;

class EmployeeController extends Controller
{
    public function __construct(
        private TransferService  $transferService,
        private WithdrawalService $withdrawalService,
        private EmployeeService  $employeeService,
    ) {}

    // ==================== Pending Approvals ====================

    /**
     * List all pending approvals (transfers + withdrawals)
     */
    public function pendingApprovals(): JsonResponse
    {
        // Get pending transfers
        $pendingTransfers = Transfer::where('status', 'pending_approval')
            ->with(['sender.user', 'receiver.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'type'         => 'transfer',
                'id'           => $t->id,
                'reference'    => $t->reference_number,
                'amount'       => $t->amount,
                'sender'       => [
                    'id'    => $t->sender->id,
                    'name'  => $t->sender->user->name,
                    'email' => $t->sender->user->email,
                ],
                'receiver'     => [
                    'id'    => $t->receiver->id,
                    'name'  => $t->receiver->user->name,
                    'email' => $t->receiver->user->email,
                ],
                'notes'        => $t->notes,
                'requested_at' => $t->created_at,
            ]);

        // Get pending withdrawals
        $pendingWithdrawals = Transaction::where('status', 'pending_approval')
            ->where('type', 'withdrawal')
            ->with('customer.user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'type'         => 'withdrawal',
                'id'           => $t->id,
                'reference'    => $t->reference_number,
                'amount'       => $t->amount,
                'customer'     => [
                    'id'    => $t->customer->id,
                    'name'  => $t->customer->user->name,
                    'email' => $t->customer->user->email,
                ],
                'description'  => $t->description,
                'requested_at' => $t->created_at,
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'transfers'   => $pendingTransfers,
                'withdrawals' => $pendingWithdrawals,
                'total'       => $pendingTransfers->count() + $pendingWithdrawals->count(),
            ],
        ]);
    }

    /**
     * Get pending transfers only
     */
    public function pendingTransfers(): JsonResponse
    {
        $transfers = Transfer::where('status', 'pending_approval')
            ->with(['sender.user', 'receiver.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'id'           => $t->id,
                'reference'    => $t->reference_number,
                'amount'       => $t->amount,
                'sender'       => [
                    'id'    => $t->sender->id,
                    'name'  => $t->sender->user->name,
                    'email' => $t->sender->user->email,
                ],
                'receiver'     => [
                    'id'    => $t->receiver->id,
                    'name'  => $t->receiver->user->name,
                    'email' => $t->receiver->user->email,
                ],
                'notes'        => $t->notes,
                'requested_at' => $t->created_at,
            ]);

        return response()->json([
            'success' => true,
            'data'    => $transfers,
        ]);
    }

    /**
     * Get pending withdrawals only
     */
    public function pendingWithdrawals(): JsonResponse
    {
        $withdrawals = Transaction::where('status', 'pending_approval')
            ->where('type', 'withdrawal')
            ->with('customer.user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'id'           => $t->id,
                'reference'    => $t->reference_number,
                'amount'       => $t->amount,
                'customer'     => [
                    'id'    => $t->customer->id,
                    'name'  => $t->customer->user->name,
                    'email' => $t->customer->user->email,
                ],
                'description'  => $t->description,
                'requested_at' => $t->created_at,
            ]);

        return response()->json([
            'success' => true,
            'data'    => $withdrawals,
        ]);
    }

    // ==================== Approve / Reject ====================

    /**
     * Approve a pending transfer
     */
    public function approveTransfer(Transfer $transfer): JsonResponse
    {
        $result = $this->transferService->approveTransfer($transfer, Auth::id());

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => $result['data'] ?? null,
        ], $result['code']);
    }

    /**
     * Reject a pending transfer
     */
    public function rejectTransfer(Request $request, Transfer $transfer): JsonResponse
    {
        $result = $this->transferService->rejectTransfer(
            $transfer,
            Auth::id(),
            $request->input('reason')
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => $result['data'] ?? null,
        ], $result['code']);
    }

    /**
     * Approve a pending withdrawal
     */
    public function approveWithdrawal(Transaction $transaction): JsonResponse
    {
        $result = $this->withdrawalService->approveWithdrawal($transaction, Auth::id());

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => $result['data'] ?? null,
        ], $result['code']);
    }

    /**
     * Reject a pending withdrawal
     */
    public function rejectWithdrawal(Request $request, Transaction $transaction): JsonResponse
    {
        if ($transaction->type !== 'withdrawal') {
            return response()->json([
                'success' => false,
                'message' => 'This is not a withdrawal transaction.',
            ], 422);
        }

        $result = $this->withdrawalService->rejectWithdrawal(
            $transaction,
            Auth::id(),
            $request->input('reason')
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => $result['data'] ?? null,
        ], $result['code']);
    }

    // ==================== Customer Management ====================

    /**
     * Search customers by name, email, or phone
     * GET /api/employee/customers/search?q=ahmed
     * GET /api/employee/customers/search?email=ahmed@mail.com
     * GET /api/employee/customers/search?phone=0912345678
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        $filters = $request->only(['q', 'name', 'identity_number']);

        if (empty($filters)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide at least one search parameter : q (general), name, or identity_number',
            ], 422);
        }

        $customers = $this->employeeService->searchCustomers($filters);

        return response()->json([
            'success' => true,
            'data'    => $customers,
            'count'   => $customers->count(),
        ]);
    }

    /**
     * View full customer details
     * GET /api/employee/customers/{id}
     */
    public function viewCustomer(Customer $customer): JsonResponse
    {
        $details = $this->employeeService->getCustomerDetails($customer);

        return response()->json([
            'success' => true,
            'data'    => $details,
        ]);
    }

    /**
     * Get customer transactions
     * GET /api/employee/customers/{id}/transactions
     */
    public function customerTransactions(CreateCustomerByEmployeeRequest $request, Customer $customer): JsonResponse
    {
        $limit = $request->input('limit', 500)->validated();
        $transactions = $this->employeeService->getCustomerTransactions($customer, $limit);

        return response()->json([
            'success' => true,
            'data'    => $transactions,
            'count'   => $transactions->count(),
        ]);
    }

    /**
     * Get customer transfers
     * GET /api/employee/customers/{id}/transfers
     */
    public function customerTransfers(Request $request, Customer $customer): JsonResponse
    {
        $limit = $request->input('limit', 50);
        $transfers = $this->employeeService->getCustomerTransfers($customer, $limit);

        return response()->json([
            'success' => true,
            'data'    => $transfers,
        ]);
    }

    public function freezeAccount(Request $request, Customer $customer): JsonResponse

    {

        $request->validate([

            'reason' => 'required|string|max:255',

        ]);


        $result = $this->employeeService->freezeAccount(

            $customer,

            Auth::id(),

            $request->input('reason')

        );


        return response()->json($result, $result['success'] ? 200 : 422);
    }


    public function unfreezeAccount(Request $request, Customer $customer): JsonResponse

    {

        $request->validate([

            'reason' => 'required|string|max:255',

        ]);


        $result = $this->employeeService->unfreezeAccount(

            $customer,

            Auth::id(),

            $request->input('reason')

        );


        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function createCustomer(CreateCustomerByEmployeeRequest $request): JsonResponse

    {

        $validatedData = $request->validated();

        $result = $this->employeeService->createCustomer($validatedData);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json($result, 201);
    }
}
