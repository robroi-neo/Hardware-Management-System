<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name','capital','unit','status'];
    protected $casts = [
        'capital' => 'float',
    ];

    public function branchInventories()
    {
        return $this->hasMany(BranchInventory::class, 'product_id');
    }

    public function salesItems()
    {
        return $this->hasMany(SaleItem::class, 'product_id');
    }

    public function scopeSearch(Builder $query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        $like = "%{$term}%";

        return $query->where(function (Builder $inner) use ($term, $like) {
            $inner->where('name', 'like', $like)
                ->orWhere('unit', 'like', $like);

            if (is_numeric($term)) {
                $inner->orWhere('id', (int) $term);
            }
        });
    }
}
