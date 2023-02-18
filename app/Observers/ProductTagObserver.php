<?php

namespace App\Observers;

use App\Models\ProductTag;
use App\Traits\Observers\ActivityLogTrait;

class ProductTagObserver
{
    use ActivityLogTrait;
    /**
     * Handle the ProductTag "created" event.
     *
     * @param  \App\Models\ProductTag  $productTag
     * @return void
     */
    public function created(ProductTag $productTag)
    {
        $this->activityLog($productTag, 'created');
    }

    /**
     * Handle the ProductTag "updated" event.
     *
     * @param  \App\Models\ProductTag  $productTag
     * @return void
     */
    public function updated(ProductTag $productTag)
    {
        $this->activityLog($productTag, 'updated');
    }

    /**
     * Handle the ProductTag "deleted" event.
     *
     * @param  \App\Models\ProductTag  $productTag
     * @return void
     */
    public function deleted(ProductTag $productTag)
    {
        $this->activityLog($productTag, 'deleted');
    }

    /**
     * Handle the ProductTag "restored" event.
     *
     * @param  \App\Models\ProductTag  $productTag
     * @return void
     */
    public function restored(ProductTag $productTag)
    {
        //
    }

    /**
     * Handle the ProductTag "force deleted" event.
     *
     * @param  \App\Models\ProductTag  $productTag
     * @return void
     */
    public function forceDeleted(ProductTag $productTag)
    {
        //
    }
}
