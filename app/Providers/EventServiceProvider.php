<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductDimension;
use App\Models\ProductImage;
use App\Models\ProductMailBox;
use App\Models\ProductMenu;
use App\Models\ProductMerchant;
use App\Models\ProductPricing;
use App\Models\ProductTag;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Observers\CategoryObserver;
use App\Observers\MenuObserver;
use App\Observers\ProductAttributeObserver;
use App\Observers\ProductImageObserver;
use App\Observers\ProductMailBoxObserver;
use App\Observers\ProductMenuObserver;
use App\Observers\ProductMerchantObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductTagObserver;
use App\Observers\RoleObserver;
use App\Observers\TagObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Category::observe(CategoryObserver::class);
        Menu::observe(MenuObserver::class);
        Product::observe(ProductObserver::class);
        ProductAttribute::observe(ProductAttributeObserver::class);
        ProductMailBox::observe(ProductMailBoxObserver::class);
        ProductImage::observe(ProductImageObserver::class);
        ProductMenu::observe(ProductMenuObserver::class);
        ProductMerchant::observe(ProductMerchantObserver::class);
        ProductTag::observe(ProductTagObserver::class);
        Role::observe(RoleObserver::class);
        Tag::observe(TagObserver::class);
        User::observe(UserObserver::class);
    }
}
