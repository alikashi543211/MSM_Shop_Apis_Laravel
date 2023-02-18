<?php
namespace App\Traits\Api;
use Illuminate\Support\Facades\File;

trait MenuTrait
{

    private function getNextMenuSortNumber()
    {
        $sort_number = 1;
        if($this->menu->newQuery()->count() > 0)
        {
            $sort_number = $this->menu->newQuery()->orderBy('sort_number', 'desc')->value('sort_number') + 1;
        }
        return $sort_number;
    }


}
