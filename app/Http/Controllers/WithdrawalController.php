<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Http\Requests\WithdrawalRequest;
use App\Services\WithdrawalService;

class WithdrawalController extends Controller
{
    public function __construct(private WithdrawalService $withdrawalService) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WithdrawalRequest $request)
    {
        $customer = Customer::where(
            'user_id',
            Auth::id()
        )->firstOrFail();

        $result = $this->withdrawalService
            ->createWithdrawal(
                $customer,
                $request->validated()
            );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => $result['data'] ?? null,
        ], $result['code']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
