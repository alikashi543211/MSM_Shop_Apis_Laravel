<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;

class ProductImage extends Model
{
    use HasFactory;

    // protected $appends = [''];
    protected $hidden = ['image_path'];
    // protected $casts = [''];
    // protected $with = [''];

    protected $fillable = [
        'product_id',
        'image_style',
        'description',
    ];

    public function getImageAttribute($value)
    {
        if(File::exists($value))
        {
            return url($value);
        }
        return null;
    }

    public function getImagePathAttribute()
    {
        if(!is_null($this->image))
        {
            $image = explode(url(''), $this->image)[1];
            if(File::exists($image))
            {
                return $image;
            }
        }
        return null;

    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
