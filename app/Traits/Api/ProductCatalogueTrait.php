<?php
namespace App\Traits\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

trait ProductCatalogueTrait
{
    private function saveMenuAndTags($inputs)
    {
        // Menus
        if(!empty($inputs['menus']))
        {
            $productMenuIds = $this->productMenu->newQuery()->whereProductId($inputs['product_id'])->pluck('id')->toArray();
            $inputsProductMenuIds = [];
            foreach($inputs['menus'] as $menuKey => $menuValue)
            {

                if(isset($menuValue['id']))
                {
                    $inputsProductMenuIds[] = $menuValue['id'];
                    $productMenu = $this->productMenu->newQuery()->whereId($menuValue['id'])->first();
                }else{
                    $productMenu = $this->productMenu->newInstance();
                }

                $productMenu->menu_id = $menuValue['menu_id'];
                $productMenu->product_id = $inputs['product_id'];
                if(!$productMenu->save())
                {
                    return false;
                }
            }

            if(!$this->deleteProductMenus($productMenuIds, $inputsProductMenuIds))
            {
                return false;
            }
        }


        // Tags
        if(!empty($inputs['tags']))
        {
            $productTagIds = $this->productTag->newQuery()->whereProductId($inputs['product_id'])->pluck('id')->toArray();
            $inputsProductTagIds = [];
            foreach($inputs['tags'] as $tagKey => $tagValue)
            {
                foreach($tagValue as $categoryTagKey => $categoryTagValue)
                {
                    if(isset($categoryTagValue['id']))
                    {
                        $inputsProductTagIds[] = $categoryTagValue['id'];
                        $productTag = $this->productTag->newQuery()->whereId($categoryTagValue['id'])->first();
                    }else{
                        $productTag = $this->productTag->newInstance();
                    }

                    $productTag->category_id = $categoryTagValue['category_id'];
                    $productTag->tag_id = $categoryTagValue['tag_id'];
                    $productTag->product_id = $inputs['product_id'];
                    if(!$productTag->save())
                    {
                        return false;
                    }
                }

            }

            if(!$this->deleteProductTags($productTagIds, $inputsProductTagIds))
            {
                return false;
            }
        }


        return true;
    }

    private function deleteProductMenus($productMenuIds, $inputsProductMenuIds)
    {
        foreach($productMenuIds as $productMenuId)
        {
            if(!in_array($productMenuId, $inputsProductMenuIds))
            {
                $productMenu = $this->productMenu->newQuery()->whereId($productMenuId)->first();
                if(!$productMenu->delete())
                {
                    return false;
                }
            }
        }
        return true;
    }

    private function deleteProductTags($productTagIds, $inputsProductTagIds)
    {
        foreach($productTagIds as $productTagId)
        {
            if(!in_array($productTagId, $inputsProductTagIds))
            {
                $productTag = $this->productTag->newQuery()->whereId($productTagId)->first();
                if(!$productTag->delete())
                {
                    return false;
                }
            }
        }
        return true;
    }

    private function deleteOldTagsAndMenus($inputs)
    {
        if($this->productTag->whereProductId($inputs['product_id'])->exists())
        {
            if(!$this->productTag->whereProductId($inputs['product_id'])->delete())
            {
                return false;
            }
        }
        if($this->productMenu->whereProductId($inputs['product_id'])->exists())
        {
            if(!$this->productMenu->whereProductId($inputs['product_id'])->delete())
            {
                return false;
            }
        }
        return true;
    }
}
