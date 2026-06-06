<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transfer(){
        return $this->belongsTo(Transfer::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === 'credit' ? '+' : '-';
        return $prefix . number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => '🟡 معلق',
            'approved' => '🟢 مقبول',
            'rejected' => '🔴 مرفوض',
            'completed' => '✅ مكتمل',
        ];
        return $badges[$this->status] ?? '⚪not defined';
    }
}
