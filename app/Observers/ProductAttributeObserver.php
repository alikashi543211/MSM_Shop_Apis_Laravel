<?php

namespace App\Observers;

use App\Models\ProductAttribute;
use App\Traits\Observers\ActivityLogTrait;

class ProductAttributeObserver
{
    use ActivityLogTrait;
    /**
     * Handle the ProductAttribute "created" event.
     *
     * @param  \App\Models\ProductAttribute  $productAttribute
     * @return void
     */
    public function created(ProductAttribute $productAttribute)
    {
        $this->activityLog($productAttribute, 'created');
    }

    /**
     * Handle the ProductAttribute "updated" event.
     *
     * @param  \App\Models\ProductAttribute  $productAttribute
     * @return void
     */
    public function updated(ProductAttribute $productAttribute)
    {
        $this->activityLog($productAttribute, 'updated');
    }

    /**
     * Handle the ProductAttribute "deleted" event.
     *
     * @param  \App\Models\ProductAttribute  $productAttribute
     * @return void
     */
    public function deleted(ProductAttribute $productAttribute)
    {
        $this->activityLog($productAttribute, 'deleted');
    }

    /**
     * Handle the ProductAttribute "restored" event.
     *
     * @param  \App\Models\ProductAttribute  $productAttribute
     * @return void
     */
    public function restored(ProductAttribute $productAttribute)
    {
        //
    }

    /**
     * Handle the ProductAttribute "force deleted" event.
     *
     * @param  \App\Models\ProductAttribute  $productAttribute
     * @return void
     */
    public function forceDeleted(ProductAttribute $productAttribute)
    {
        //
    }
}
