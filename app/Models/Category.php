<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'user_id',
        'menu_id',
        'sort_number',
    ];

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class, 'category_id', 'id');
    }

    public function productTags(): HasMany
    {
        return $this->hasMany(ProductTag::class, 'category_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function category_products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tags', 'category_id', 'product_id')->distinct();
    }

    public function activeCategoryProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tags', 'category_id', 'product_id')->distinct()->where('is_active', 1)->where(function($q){
            $q->where(function($q){
                $q->whereNull('published_at')->orWhere('published_at', '<=', Carbon::now()->format('Y-m-d'));
            });
            $q->where(function($q){
                $q->whereNull('expired_at')->orWhere('expired_at', '>', Carbon::now()->format('Y-m-d'));
            });
        });
    }

    /**
     * Get the user that owns the Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

}
