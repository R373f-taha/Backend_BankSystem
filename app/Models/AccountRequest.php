<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountRequest extends Model
{
    protected $table = 'account_requests';

    protected $fillable = [
        'full_name',
        'date_of_birth',
        'gender',
        'marital_status',
        'identity_number',
        'address',
        'occupation',
        'deposit_amount',
        'status',
        'unique_link',
        'verification_code',
        'admin_id',
        'email',
        'admin_notes',
        'verified_at',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
}