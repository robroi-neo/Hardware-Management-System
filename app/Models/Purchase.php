<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'branch_id',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the supplier for this purchase
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the branch for this purchase
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all line items (purchase details) for this purchase
     */
    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    /**
     * Get the invoice for this purchase (if exists)
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get all products in this purchase (through details)
     */
    public function products()
    {
        return $this->hasManyThrough(Product::class, PurchaseDetail::class, 'purchase_id', 'id', 'id', 'product_id');
    }

    /**
     * Calculate total amount from all purchase details
     */
    public function getTotalAmount()
    {
        return $this->details()->sum('subtotal');
    }
}
