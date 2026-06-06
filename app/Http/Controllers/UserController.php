<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\TransactionService;

class UserController extends Controller
{
    protected $userService;
    protected $transactionService;

    public function __construct(UserService $userService, TransactionService $transactionService)
    {
        $this->userService = $userService;
        $this->transactionService = $transactionService;
    }
    public function index()
    {
        $users = User::all(['id', 'name', 'email']);
        return response()->json($users);
    }
    public function getLatestTransactions()
    {

        $user = Auth::user();


        $customer = $user->customer;


        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'This user does not have an associated customer account.',
                'data' => []
            ], 404);
        }


  $transactions = $this->transactionService->getLatestTransactions($customer->id);

        return response()->json([
            'success' => true,
            'message' => 'Latest 6 transactions retrieved successfully with details.',
            'data' => $transactions
        ], 200);
    }
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $isChanged = $this->userService->changePassword($user, $request->current_password, $request->new_password);

        if (!$isChanged) {
            return response()->json([
                'success' => false,
                'message' => 'The current password you entered is incorrect.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.'
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $updatedUser = $this->userService->updateProfile($user, $request->only(['name', 'email']));

        return response()->json([
            'success' => true,
            'message' => 'Profile information updated successfully.',
            'data' => [
                'name' => $updatedUser->name,
                'email' => $updatedUser->email
            ]
        ], 200);
    }
}
