<?php

namespace App\Observers;

use App\Models\ProductMailBox;
use App\Traits\Observers\ActivityLogTrait;

class ProductMailBoxObserver
{
    use ActivityLogTrait;
    /**
     * Handle the ProductImage "created" event.
     *
     * @param  \App\Models\ProductMailBox  $productMailBox
     * @return void
     */
    public function created(ProductMailBox $productMailBox)
    {
        $this->activityLog($productMailBox, 'created');
    }

    /**
     * Handle the ProductMailBox "updated" event.
     *
     * @param  \App\Models\ProductMailBox  $productImage
     * @return void
     */
    public function updated(ProductMailBox $productMailBox)
    {
        $this->activityLog($productMailBox, 'updated');
    }

    /**
     * Handle the ProductMailBox "deleted" event.
     *
     * @param  \App\Models\ProductMailBox  $productImage
     * @return void
     */
    public function deleted(ProductMailBox $productMailBox)
    {
        $this->activityLog($productMailBox, 'deleted');
    }

    /**
     * Handle the ProductMailBox "restored" event.
     *
     * @param  \App\Models\ProductMailBox  $productMailBox
     * @return void
     */
    public function restored(ProductMailBox $productMailBox)
    {
        //
    }

    /**
     * Handle the ProductMailBox "force deleted" event.
     *
     * @param  \App\Models\ProductMailBox  $productMailBox
     * @return void
     */
    public function forceDeleted(ProductMailBox $productMailBox)
    {
        //
    }
}
