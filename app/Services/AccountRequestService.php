<?php

namespace App\Services;

use App\Events\AccountRequestAcceptedEvent;
use App\Models\AccountRequest;
use Illuminate\Support\Str;

class AccountRequestService
{
    /**
     * Create a new account request
     */
    public function create(array $data): AccountRequest
    {
        $data['unique_link']=Str::random(32);

        return AccountRequest::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'marital_status' => $data['marital_status'],
            'identity_number' => $data['identity_number'],
            'address' => $data['address'],
            'occupation' => $data['occupation'],
            'deposit_amount' => $data['deposit_amount'],
            'status' => AccountRequest::STATUS_PENDING,
        ]);
    }

    public function acceptRequest(AccountRequest $accountRequest){

        $verificationCode=Str::random(8);

        $accountRequest->update([
              'status'=>'accepted',
               'verification_code'=>$verificationCode,
               'verified_at'=>null
        ]);
      AccountRequestAcceptedEvent::dispatch($accountRequest->fresh());
        return $accountRequest->fresh();
    }

     public function verify(array $data): array
    {
        $accountRequest = AccountRequest::where('email', $data['email'])
            ->where('verification_code', $data['verification_code'])
            ->first();

        if (!$accountRequest) {
            return [
                'success' => false,
                'status' => 'not_found',
                'message' => 'The email or verification code is incorrect.',
                'code' => 404
            ];
        }


        return match ($accountRequest->status) {
            'pending' => [
                'success' => false,
                'status' => 'pending',
                'message' => 'Your request is still under review, please wait⌛',
                'code' => 200
            ],

            'rejected' => [
                'success' => false,
                'status' => 'rejected',
                'message' => 'We are sorry, your request has been rejected. ❌',
                'reason' => $accountRequest->admin_notes,
                'code' => 200
            ],

            'accepted' => $this->handleAccepted($accountRequest),

            default => [
                'success' => false,
                'status' => 'unknown',
                'message' => 'Request status unknown.🤔🤔',
                'code' => 400
            ]
        };
    }

    private function handleAccepted(AccountRequest $accountRequest): array
    {
        if ($accountRequest->verified_at) {
            return [
                'success' => false,
                'status' => 'already_verified',
                'message' => 'Your account has already been verified.😑😑',
                'code' => 200
            ];
        }


        $accountRequest->update(['verified_at' => now()]);

        return [
            'success' => true,
            'status' => 'verified',
            'message' => 'Your account has been successfully verified! ✅💛🎉',
            'data' => [
                'full_name' => $accountRequest->full_name,
                'email' => $accountRequest->email,
                'verified_at' => $accountRequest->verified_at
            ],
            'code' => 200
        ];

    }

    public function rejectRequest(AccountRequest $accountRequest,?string $notes){

      if ($accountRequest->status !== 'pending') {

       throw new \Exception('this request is '.$accountRequest->status.' so we cannot reject it 😑😒 ');
    }


     $accountRequest->update([
            'status' => 'rejected',
            'admin_notes' => $notes,
            'verification_code' => null,
        ]);

     return $accountRequest->fresh();

    }
}
