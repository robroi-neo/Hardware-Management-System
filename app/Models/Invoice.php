<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['purchase_id','date_issued','total_amount','date_due','refunded','refunded_by','refunded_at'];
    protected $casts = [
        'total_amount' => 'float',
        'date_issued' => 'datetime',
        'date_due' => 'datetime',
        'refunded' => 'boolean',
        'refunded_at' => 'datetime',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function refundedBy()
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }
}
