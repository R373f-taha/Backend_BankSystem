<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Models\Customer;
use App\Models\Transfer;
use App\Services\TransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferController extends Controller
{

 public function __construct(private TransferService $transferService) {}
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
    public function store(TransferRequest $request)
    {
        $sender=Customer::where('user_id',Auth::user()->id)->firstOrFail();

        $receiver=Customer::where('email',$request->receive_email)->firstOrFail();

        $result=$this->transferService->executeTransfer($sender,$receiver,$request->amount,$request->notes);

         return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => $result['data'] ?? null,
        ], $result['code']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transfer $transfer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transfer $transfer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transfer $transfer)
    {
        //
    }
}
