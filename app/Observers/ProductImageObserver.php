<?php

namespace App\Observers;

use App\Models\ProductImage;
use App\Traits\Observers\ActivityLogTrait;

class ProductImageObserver
{
    use ActivityLogTrait;
    /**
     * Handle the ProductImage "created" event.
     *
     * @param  \App\Models\ProductImage  $productImage
     * @return void
     */
    public function created(ProductImage $productImage)
    {
        $this->activityLog($productImage, 'created');
    }

    /**
     * Handle the ProductImage "updated" event.
     *
     * @param  \App\Models\ProductImage  $productImage
     * @return void
     */
    public function updated(ProductImage $productImage)
    {
        $this->activityLog($productImage, 'updated');
    }

    /**
     * Handle the ProductImage "deleted" event.
     *
     * @param  \App\Models\ProductImage  $productImage
     * @return void
     */
    public function deleted(ProductImage $productImage)
    {
        $this->activityLog($productImage, 'deleted');
    }

    /**
     * Handle the ProductImage "restored" event.
     *
     * @param  \App\Models\ProductImage  $productImage
     * @return void
     */
    public function restored(ProductImage $productImage)
    {
        //
    }

    /**
     * Handle the ProductImage "force deleted" event.
     *
     * @param  \App\Models\ProductImage  $productImage
     * @return void
     */
    public function forceDeleted(ProductImage $productImage)
    {
        //
    }
}
