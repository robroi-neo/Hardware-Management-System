<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $table = 'sales_items';
    protected $fillable = ['sale_id','product_id','quantity','unit_price','unit_cost','markup','subtotal'];
    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'float',
        'unit_cost' => 'float',
        'markup' => 'float',
        'subtotal' => 'float',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
