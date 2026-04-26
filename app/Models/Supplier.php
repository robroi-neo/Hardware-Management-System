<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    //
    protected $fillable = [
        'supplier_name',
        'contact_person',
        'company_address',
        'contact_number',
        'contact_email',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, Purchase::class);
    }

    public function purchaseDetails()
    {
        return $this->hasManyThrough(PurchaseDetail::class, Purchase::class);
    }
}
