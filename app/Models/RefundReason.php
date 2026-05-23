<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundReason extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'refund_reason_id');
    }
}
