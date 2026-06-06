<?php

namespace App\Services;

use App\Events\TransferCompletedEvent;
use App\Models\AccountRequest;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferService{

public function executeTransfer(Customer $sender,Customer $receiver
,float $amount,?string $notes=null){

if($sender->balance<$amount){

return [
    'success'=>false,
    'message'=>'Insufficient balance.You don`t have enough funds to complete this transfer😒😒',
    'code'=>422
];
}

if($sender->user_id===$receiver->user_id){
    return [
    'success'=>false,
    'message'=>'You cannot transfer money to your own account😐❌',
    'code'=>422
    ];
}

if($sender->status!=='active' || $receiver->status !=='active'){

return[
    'success'=>false,
    'message'=>'Both accounts must be active to complete the transfer😑❌',
    'code'=>422
];
}
$transfer=DB::transaction(function () use ($sender, $receiver, $amount, $notes){

$transfer=Transfer::create([
         'reference_number' => 'TRF-' . strtoupper(Str::random(10)),
                'sender_id'        => $sender->id,
                'receiver_id'      => $receiver->id,
                'amount'           => $amount,
                'notes'            => $notes,
                'status'           => 'pending',
]);

Transaction::create([
        'reference_number' => 'TXN-' . strtoupper(Str::random(10)),
                'customer_id'      => $sender->id,
                'amount'           => $amount,
                'type'             => 'debit',
                'description'      =>  "Transfer to {$receiver->user->name} ({$receiver->user->email})",
                'status'           => 'completed',
                'transfer_id'      => $transfer->id,
]);

$sender->decrement('balance',$amount);

Transaction::create([
             'reference_number' => 'TXN-' . strtoupper(Str::random(10)),
                'customer_id'      => $receiver->id,
                'amount'           => $amount,
                'type'             => 'credit',
                'description'      =>  "Transfer received from {$sender->user->name} ({$sender->user->email})",
                'status'           => 'completed',
                'transfer_id'      => $transfer->id,
]);

$receiver->increment('balance',$amount);

$transfer->update([
    'status'=>'completed',
    'completed_at'=>now()
]);

return $transfer;


});


event(new TransferCompletedEvent($transfer));

  return [
            'success' => true,
            'message' => 'Transfer completed successfully.',
            'data'    => $transfer->fresh()->load(['sender.user', 'receiver.user']),
            'code'    => 201,
        ];
}

}
