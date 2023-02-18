<?php
namespace App\Traits\Api;


trait CategoryTrait
{

    private function getNextCategorySortNumber($menuId)
    {
        $sort_number = 1;
        if($this->category->newQuery()->whereMenuId($menuId)->count() > 0)
        {
            $sort_number = $this->category->newQuery()->whereMenuId($menuId)->orderBy('sort_number', 'desc')->value('sort_number') + 1;
        }
        return $sort_number;
    }

    private function getNatSortedCategoryTags($categoryId)
    {
        $tags = $this->tag->newQuery()->whereCategoryId($categoryId)->get()->sortBy('title', SORT_NATURAL, false)->toArray();
        return array_values($tags);
    }

    private function getNatSortedCategoryTagsForMenu($categoryTitle, $uniqueTagTitleList = null)
    {
        $query = $this->tag->newQuery()->whereHas('category', function($q) use($categoryTitle) {
            $q->whereTitle($categoryTitle);
        })->has('activeTagProducts');
        if($uniqueTagTitleList)
        {
            $query->whereIn('title', $uniqueTagTitleList);
        }
        if(!empty(request('menu_id')))
        {
            $query->withCount(['activeTagProducts as product_tags_count' => function($q){
                $q->whereHas('product_menus', function($q){
                    $q->where('menus.id', request('menu_id'));
                });
            }]);
        }else{
            $query->withCount(['activeTagProducts as product_tags_count']);
        }

        $tags = $query->pluck('title')->toArray();
        natcasesort($tags);
        return array_values(array_unique($tags));
    }

    private function getNatSortedCategoryTagsForMenuMobile($categoryTitle, $uniqueTagTitleList = null)
    {
        $query = $this->tag->newQuery()->whereHas('category', function($q) use($categoryTitle) {
            $q->whereIn('title', $categoryTitle);
        })->has('activeTagProducts');
        if($uniqueTagTitleList)
        {
            $query->whereIn('title', $uniqueTagTitleList);
        }
        if(!empty(request('menu_id')))
        {
            $query->withCount(['activeTagProducts as product_tags_count' => function($q){
                $q->whereHas('product_menus', function($q){
                    $q->where('menus.id', request('menu_id'));
                });
            }]);
        }else{
            $query->withCount(['activeTagProducts as product_tags_count']);
        }

        $tags = $query->pluck('title')->toArray();
        natcasesort($tags);
        return array_values(array_unique($tags));
    }

    private function getNatSortedCategoryTagsForMenuAdmin($categoryId, $tagIds = null)
    {
        $query = $this->tag->newQuery()->whereCategoryId($categoryId)->has('tag_products');
        if($tagIds)
        {
            $query->whereIn('id', $tagIds);
        }
        if(!empty(request('menu_id')))
        {
            $query->withCount(['tag_products as product_tags_count' => function($q){
                $q->whereHas('product_menus', function($q){
                    $q->where('menus.id', request('menu_id'));
                });
            }]);
        }else{
            $query->withCount(['tag_products as product_tags_count']);
        }
        $tags = $query->get()->sortBy('title', SORT_NATURAL, false)->toArray();
        return array_values($tags);
    }

    private function validateSizeOfIds($inputs)
    {
        $count = $this->category->newQuery()->whereMenuId($inputs['menu_id'])->count();
        $idsCount = count($inputs['ids']);
        if($count != $idsCount)
        {
            return [false, $count];
        }
        return [true];
    }

}
