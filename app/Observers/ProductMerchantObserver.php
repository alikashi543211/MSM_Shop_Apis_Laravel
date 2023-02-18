<?php

namespace App\Observers;

use App\Models\ProductMerchant;
use App\Traits\Observers\ActivityLogTrait;

class ProductMerchantObserver
{
    use ActivityLogTrait;
    /**
     * Handle the ProductImage "created" event.
     *
     * @param  \App\Models\ProductMerchant  $productMerchant
     * @return void
     */
    public function created(ProductMerchant $productMerchant)
    {
        $this->activityLog($productMerchant, 'created');
    }

    /**
     * Handle the ProductMailBox "updated" event.
     *
     * @param  \App\Models\ProductMailBox  $productImage
     * @return void
     */
    public function updated(ProductMerchant $productMerchant)
    {
        $this->activityLog($productMerchant, 'updated');
    }

    /**
     * Handle the ProductMailBox "deleted" event.
     *
     * @param  \App\Models\ProductMailBox  $productImage
     * @return void
     */
    public function deleted(ProductMerchant $productMerchant)
    {
        $this->activityLog($productMerchant, 'deleted');
    }

    /**
     * Handle the ProductMailBox "restored" event.
     *
     * @param  \App\Models\ProductMailBox  $productMailBox
     * @return void
     */
    public function restored(ProductMerchant $productMerchant)
    {
        //
    }

    /**
     * Handle the ProductMailBox "force deleted" event.
     *
     * @param  \App\Models\ProductMailBox  $productMailBox
     * @return void
     */
    public function forceDeleted(ProductMerchant $productMerchant)
    {
        //
    }
}
