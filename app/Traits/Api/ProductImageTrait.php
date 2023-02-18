<?php
namespace App\Traits\Api;


trait ProductImageTrait
{

    private function getNextProductImageSortNumber($productId)
    {
        $sort_number = 1;
        if($this->productImage->newQuery()->whereProductId($productId)->count() > 0)
        {
            $sort_number = $this->productImage->newQuery()->whereProductId($productId)->orderBy('sort_number', 'desc')->value('sort_number') + 1;
        }
        return $sort_number;
    }

    private function validateSizeOfIds($inputs)
    {
        $count = $this->productImage->whereProductId($inputs['product_id'])->count();
        $idsCount = count($inputs['ids']);
        if($count != $idsCount)
        {
            return [false, $count];
        }
        return [true];
    }

}
