<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'product_id',
    ];
    protected $hidden = [
        'key',
        'value',
    ];

    protected $appends = ['attribute'];

    public function getAttributeAttribute()
    {
        $data['key'] = $this->key;
        $data['value'] = $this->value;
        return $data;
    }


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
