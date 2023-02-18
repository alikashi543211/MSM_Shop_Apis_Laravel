<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{

    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProductTag::class, 'tag_id', 'id');
    }

    public function productTags(): HasMany
    {
        return $this->hasMany(ProductTag::class, 'tag_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function tag_products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tags', 'tag_id', 'product_id');
    }

    public function activeTagProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tags', 'tag_id', 'product_id')->distinct()->where('is_active', 1)->where(function($q){
            $q->where(function($q){
                $q->whereNull('published_at')->orWhere('published_at', '<=', Carbon::now()->format('Y-m-d'));
            });
            $q->where(function($q){
                $q->whereNull('expired_at')->orWhere('expired_at', '>', Carbon::now()->format('Y-m-d'));
            });
        });
    }

}
