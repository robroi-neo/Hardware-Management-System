<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name','capital','unit'];
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

    // Unit rules define how quantities should be handled per unit.
    // - step: recommended input step for the UI (not used server-side here)
    // - precision: number of decimal places allowed
    // - allow_fraction: whether fractional quantities are allowed
    public const UNIT_RULES = [
        'pcs' => ['step' => 1, 'precision' => 0, 'allow_fraction' => false],
        'piece' => ['step' => 1, 'precision' => 0, 'allow_fraction' => false],
        'box' => ['step' => 1, 'precision' => 0, 'allow_fraction' => false],
        'meter' => ['step' => 0.01, 'precision' => 2, 'allow_fraction' => true],
        'm' => ['step' => 0.01, 'precision' => 2, 'allow_fraction' => true],
        'liter' => ['step' => 0.01, 'precision' => 2, 'allow_fraction' => true],
        'l' => ['step' => 0.01, 'precision' => 2, 'allow_fraction' => true],
        'kg' => ['step' => 0.01, 'precision' => 2, 'allow_fraction' => true],
        'gram' => ['step' => 0.001, 'precision' => 3, 'allow_fraction' => true],
        'g' => ['step' => 0.001, 'precision' => 3, 'allow_fraction' => true],
    ];

    public static function getUnitRule(?string $unit): array
    {
        if (empty($unit)) {
            return ['step' => 1, 'precision' => 0, 'allow_fraction' => false];
        }

        $key = strtolower(trim($unit));
        return self::UNIT_RULES[$key] ?? ['step' => 1, 'precision' => 0, 'allow_fraction' => false];
    }

    public static function roundQuantity(float $quantity, ?string $unit): float
    {
        $rule = self::getUnitRule($unit);
        $precision = (int) ($rule['precision'] ?? 0);
        if ($precision <= 0) {
            return (float) floor($quantity);
        }
        $factor = pow(10, $precision);
        return (float) (round($quantity * $factor) / $factor);
    }

    public static function hasValidPrecision(float $quantity, ?string $unit): bool
    {
        $rule = self::getUnitRule($unit);
        $precision = (int) ($rule['precision'] ?? 0);
        if ($precision <= 0) {
            // must be integer
            return abs($quantity - floor($quantity)) < 0.0000001;
        }
        $factor = pow(10, $precision);
        $mult = $quantity * $factor;
        return abs($mult - round($mult)) < 0.0000001;
    }
}
