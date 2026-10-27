<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $table = 'inventory_movements';
    protected $fillable = [
        'product_id',
        'branch_id',
        'user_id',
        'type',
        'quantity_change',
        'reference_type',
        'reference_id',
    ];

    public $timestamps = true;
    protected $casts = [
        'quantity_change' => 'float',
        'created_at' => 'datetime',
    ];

    /**
     * Log a stock change. Positive $change is stock in, negative is stock out.
     */
    public static function record(int $branchId, int $productId, float $change, string $referenceType, ?int $referenceId, ?int $userId): self
    {
        return self::create([
            'branch_id' => $branchId,
            'product_id' => $productId,
            'user_id' => $userId,
            'type' => $change >= 0 ? 'in' : 'out',
            'quantity_change' => $change,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

