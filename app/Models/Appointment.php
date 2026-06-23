<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'account_request_id',
        'appointment_time',
        'status',
        'notes',
    ];

    public function accountRequest()
    {
        return $this->belongsTo(AccountRequest::class);
    }
}
