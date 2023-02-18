<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMailBox extends Model
{
    use HasFactory;

    // protected $appends = [''];
    // protected $hidden = [''];
    // protected $casts = [''];
    // protected $with = [''];

    protected $fillable = [
        'sku',
        'landed_cost',
        'location',
        'stock',
        'discount_type',
        'discount',
        'product_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function getCostAfterDiscountAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }

    public function getDiscountAmountAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }
    public function getDiscountAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }
    public function getLandedCostAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }

    public function setStockAttribute($value)
    {
        (integer) $value;
    }

}
