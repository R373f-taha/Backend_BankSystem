<?php

namespace App\Http\Controllers;

use App\Events\AccountRequestAcceptedEvent;
use App\Models\AccountRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AccountRequestService;
use App\Http\Requests\AccounRequest\AccountRequestFormRequest;
use App\Http\Requests\AccounRequest\VerificationFormRequest;

class AccountRequestController extends Controller
{

    public function __construct(
        private AccountRequestService $service
    ) {}

    public function index(){

    return   response()->json(['message'=>'All Requests',
    'data'=>AccountRequest::all()],

    200);
    }
    /**
     * [CUSTOMER] Create account request with all data
     * POST /api/account-request
     */
    public function create(AccountRequestFormRequest $request): JsonResponse
    {
        $accountRequest = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => [
                'message'=>'Dear user...your request has been registered with us 📥,so please wait for it to be reviewed by the responsible administrator .',
                'status' => $accountRequest->status,

            ],
        ], 201);
    }

    public function acceptRequest(AccountRequest $accountRequest){

    $accountRequest=$this->service->acceptRequest($accountRequest);

    if($accountRequest!=='pending'){

    }

    event(new AccountRequestAcceptedEvent($accountRequest));

         return response()->json([
            'success' => true,
            'message' => 'Congratulations🎉💛, your account has been accepted, but you still need to verify it by clicking the verification button,Then enjoy a great experience with our system....💛😎',
            'data' => [
                'verification_code' => $accountRequest->verification_code
            ]
        ]);
    }

   public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string'
        ]);

        $result = $this->service->verify($request->only(['email', 'verification_code']));

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
            'reason' => $result['reason'] ?? null
        ], $result['code']);
    }


public function reject(Request $request, AccountRequest $accountRequest): JsonResponse
{
    try {
        $accountRequest = $this->service->rejectRequest(
            $accountRequest,
            $request->admin_notes // nullable
        );

        return response()->json([
            'success' => true,
            'message' => 'request rejected successfully',
            'data' => $accountRequest
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 422);
    }
}
}
