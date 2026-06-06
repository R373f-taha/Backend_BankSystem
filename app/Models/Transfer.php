<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable=[
        'sender_id','receiver_id'
        ,'amount','reference_number','notes','status'
        ,'completed_at'
    ];

       protected $casts = [
        'amount' => 'decimal:2',
        'completed_at' => 'datetime'
    ];

    public function transactions(){

    return $this->hasMany(Transaction::class);
    }
 public function sender()
    {
        return $this->belongsTo(Customer::class, 'sender_id');
    }

    /**
     * Get the receiver customer.
     */
    public function receiver()
    {
        return $this->belongsTo(Customer::class, 'receiver_id');
    }

}
