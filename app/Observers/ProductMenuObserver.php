<?php

namespace App\Observers;

use App\Models\ProductMenu;
use App\Traits\Observers\ActivityLogTrait;

class ProductMenuObserver
{
    use ActivityLogTrait;
    /**
     * Handle the ProductMenu "created" event.
     *
     * @param  \App\Models\ProductMenu  $productMenu
     * @return void
     */
    public function created(ProductMenu $productMenu)
    {
        $this->activityLog($productMenu, 'created');
    }

    /**
     * Handle the ProductMenu "updated" event.
     *
     * @param  \App\Models\ProductMenu  $productMenu
     * @return void
     */
    public function updated(ProductMenu $productMenu)
    {
        $this->activityLog($productMenu, 'updated');
    }

    /**
     * Handle the ProductMenu "deleted" event.
     *
     * @param  \App\Models\ProductMenu  $productMenu
     * @return void
     */
    public function deleted(ProductMenu $productMenu)
    {
        $this->activityLog($productMenu, 'deleted');
    }

    /**
     * Handle the ProductMenu "restored" event.
     *
     * @param  \App\Models\ProductMenu  $productMenu
     * @return void
     */
    public function restored(ProductMenu $productMenu)
    {
        //
    }

    /**
     * Handle the ProductMenu "force deleted" event.
     *
     * @param  \App\Models\ProductMenu  $productMenu
     * @return void
     */
    public function forceDeleted(ProductMenu $productMenu)
    {
        //
    }
}
