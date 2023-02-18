<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;

class Menu extends Model
{
    use HasFactory;

    // protected $appends = [''];
    protected $hidden = ['image_path'];
    // protected $casts = [''];
    // protected $with = [''];

    protected $fillable = [
        'title',
        'slug',
        'image_style',
        'text_color',
        'background_color',
        'user_id',
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

    public function productMenus(): HasMany
    {
        return $this->hasMany(ProductMenu::class, 'menu_id', 'id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'menu_id', 'id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class,'product_menus', 'menu_id', 'product_id');
    }

    public function active_products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class,'product_menus', 'menu_id', 'product_id')->distinct()->where('is_active', 1)->where(function($q){
                $q->orWhereHas('productMailBoxes')->orWhereHas('productMerchants');
            })->where(function($q){
                $q->where(function($q){
                    $q->whereNull('published_at')->orWhere('published_at', '<=', Carbon::now()->format('Y-m-d'));
                });
                $q->where(function($q){
                    $q->whereNull('expired_at')->orWhere('expired_at', '>', Carbon::now()->format('Y-m-d'));
                });
            });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
