<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMerchant extends Model
{
    use HasFactory;

    // protected $appends = [''];
    // protected $hidden = [''];
    // protected $casts = [''];
    // protected $with = [''];

    protected $fillable = [
        'merchant_id',
        'product_id',
        'sort_number',
        'link',
        'retail_cost',
        'duty',
        'wharfage',
        'shipping',
        'fuel_adjustment',
        'insurance',
        'estimated_landed_cost',
        'duty_percentage',
        'weight',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'id');
    }

    public function getDutyPercentageAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }

    public function getRetailCostAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }

    public function getImportTaxesAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }
    public function getDutyAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }
    public function getWharfageAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }
    public function getShippingChargesAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }
    public function getShippingAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }
    public function getFuelAdjustmentAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }
    public function getInsuranceAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }
    public function getEstimatedLandedCostAttribute($value)
    {
        if(isset($value))
        {
            return twoDecimal($value);
        }
        return null;
    }

}
