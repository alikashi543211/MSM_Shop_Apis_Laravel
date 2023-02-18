<?php
namespace App\Traits\Api;

use Carbon\Carbon;

trait TagTrait
{
    private function isTagDeletedFromProductTags($tagId)
    {
        if($this->productTag->newQuery()->whereTagId($tagId)->count() > 0)
        {
            if(!$this->productTag->newQuery()->whereTagId($tagId)->delete())
            {
                return false;
            }
        }
        return true;
    }

}
