<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'published_at',
        'expired_at',
        'in_stock',
        'is_active',
        'is_buy_now',
        'user_id',
        'show_buying_options'
    ];

    public function getInStockAttribute()
    {
        return (integer) DB::table('product_mail_boxes')->whereProductId($this->id)->sum('stock');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class, 'product_id', 'id');
    }

    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id')->orderBy('sort_number', 'asc');
    }

    public function productMenus(): HasMany
    {
        return $this->hasMany(ProductMenu::class, 'product_id', 'id');
    }

    public function productMenuListgit(): HasMany
    {
        return $this->hasMany(ProductMenu::class, 'product_id', 'id');
    }

    public function productTags(): HasMany
    {
        return $this->hasMany(ProductTag::class, 'product_id', 'id');
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductTag::class, 'category_id', 'id');
    }

    public function product_categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_tags', 'product_id', 'category_id')->distinct();
    }

    public function product_tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tags', 'product_id', 'tag_id')->withPivot('id');
    }

    public function product_menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'product_menus', 'product_id', 'menu_id')->withPivot('id');
    }

    public function productMerchants(): HasMany
    {
        return $this->hasMany(ProductMerchant::class, 'product_id', 'id')->orderBy('sort_number', 'asc');
    }

    public function productMailBoxes(): HasMany
    {
        return $this->hasMany(ProductMailBox::class, 'product_id', 'id');
    }
}
