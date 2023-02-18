<?php
namespace App\Traits\Api;

use App\Models\Category;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\ProductMailBox;
use App\Models\ProductMenu;
use App\Models\ProductMerchant;
use App\Models\ProductTag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait ProductTrait
{
    private function saveProductAttributes($inputs, $productId)
    {
        foreach($inputs['product_attributes'] as $attribute)
        {
            foreach($attribute as $key => $value)
            {
                if(isset($value['id']))
                {
                    $attribute = $this->productAttribute->newQuery()->whereId($value['id'])->first();
                }else{
                    $attribute = $this->productAttribute->newInstance();
                }

                $attribute->fill($value);
                $attribute->product_id = $productId;
                if(!$attribute->save())
                {
                    return false;
                }
            }
        }

        return true;
    }

    private function getProductDetail($productId)
    {
        $product = $this->product->newQuery()->whereId($productId)
            ->with(['productAttributes' => function($q){
                $q->orderBy('id', 'ASC');
            },
            'product_menus', 'product_categories', 'productImages', 'productMerchants' => function($q){
                $q->with('merchant');
            }, 'productMailBoxes'])
            ->withCount(['productAttributes', 'product_menus', 'productImages', 'productMerchants', 'productMailBoxes'])
            ->first();
        $product->product_tags = $this->getNatSortedProductTags($productId);
        $product->product_tags_count = count($product->product_tags);
        $product->product_categories_count = $this->getProductCategoryCount($product['id']);
        return $product;
    }

    private function cloneProduct($inputs)
    {
        $product = $this->product->newQuery()->whereId($inputs['id'])->first();
        // Product Replicate
        $newProduct = $product->replicate();
        if(!$newProduct->push())
        {
            return [false];
        }
        $newProduct->slug = getUniqueSlug($newProduct->title);
        if(!$newProduct->save())
        {
            return [false];
        }

        // Product Attributes Replicate
        foreach($product->productAttributes as $attributeValue)
        {
            $newAttribute = $attributeValue->replicate();
            if(!$newAttribute->push())
            {
                return [false];
            }
            $newAttribute->product_id = $newProduct->id;
            if(!$newAttribute->save())
            {
                return [false];
            }
        }

        // Product Menus Replicate
        foreach($product->productMenus as $menuValue)
        {
            $newMenu = $menuValue->replicate();
            if(!$newMenu->push())
            {
                return [false];
            }
            $newMenu->product_id = $newProduct->id;
            if(!$newMenu->save())
            {
                return [false];
            }
        }

        // Product Tags Replicate
        foreach($product->productTags as $tagValue)
        {
            $newTag = $tagValue->replicate();
            if(!$newTag->push())
            {
                return [false];
            }
            $newTag->product_id = $newProduct->id;
            if(!$newTag->save())
            {
                return [false];
            }
        }

        // Product Images Replicate
        foreach($product->productImages as $imageValue)
        {
            $newImage = $imageValue->replicate();
            if(!$newImage->push())
            {
                return [false];
            }
            $newImage->product_id = $newProduct->id;
            if(!$newImage->save())
            {
                return [false];
            }
        }

        // Product Merchants Replicate
        foreach($product->productMerchants as $merchantValue)
        {
            $newMerchant = $merchantValue->replicate();
            if(!$newMerchant->push())
            {
                return [false];
            }
            $newMerchant->product_id = $newProduct->id;
            if(!$newMerchant->save())
            {
                return [false];
            }
        }

        // Product Mailboxes Replicate
        foreach($product->productMailBoxes as $mailBoxValue)
        {
            $newMailBox = $mailBoxValue->replicate();
            if(!$newMailBox->push())
            {
                return [false];
            }
            $newMailBox->product_id = $newProduct->id;
            if(!$newMailBox->save())
            {
                return [false];
            }
        }
        return [true, $newProduct->id];
    }

    private function getNatSortedProductTags($productId)
    {
        $product = $this->product->newQuery()->whereId($productId)->first();
        $product_tags = $product->product_tags()->has('category', '>', 0)->with('category')->get()->sortBy('title', SORT_NATURAL, false)->toArray();
        return array_values($product_tags);
    }

    private function getProductCategoryCount($productId)
    {
        $cat = $this->productTag->whereProductId($productId)->distinct()->pluck('category_id')->toArray();
        return count($cat);

    }

    private function searchProduct($query, $inputs, $explod)
    {
        $query->whereFuzzy(function ($q) use ($inputs, $explod) {
            searchTableFuzzy($q, $inputs['search'], ['title', 'description'], NULL, $explod);
        });

        searchTableFuzzy($query, $inputs['search'], ['key', 'value'], 'productAttributes', $explod);
        searchTableFuzzy($query, $inputs['search'], ['title'], 'product_menus', $explod);
        searchTableFuzzy($query, $inputs['search'], ['title'], 'product_tags', $explod);
        searchTableFuzzy($query, $inputs['search'], ['title'], 'product_categories', $explod);
        searchTableFuzzy($query, $inputs['search'], ['link', 'retail_cost', 'import_taxes', 'duty', 'wharfage', 'shipping_charges', 'shipping', 'fuel_adjustment', 'insurance', 'estimated_landed_cost'], 'productMerchants', $explod);
        searchTableFuzzy($query, $inputs['search'], ['location', 'sku', 'landed_cost'], 'productMailBoxes', $explod);

        return $query;
    }

    private function searchProductWithoutFuzzy($query, $keyword)
    {
        searchTable($query, $keyword, ['title', 'description'], NULL);
        searchTable($query, $keyword, ['key', 'value'], 'productAttributes');
        searchTable($query, $keyword, ['title'], 'product_menus');
        searchTable($query, $keyword, ['title'], 'product_tags');
        searchTable($query, $keyword, ['title'], 'product_categories');
        searchTable($query, $keyword, ['link', 'retail_cost', 'import_taxes', 'duty', 'wharfage', 'shipping_charges', 'shipping', 'fuel_adjustment', 'insurance', 'estimated_landed_cost'], 'productMerchants');
        searchTable($query, $keyword, ['location', 'sku', 'landed_cost'], 'productMailBoxes');

        // return $query;
    }

    private function getSearchTerms($input_search, &$search_terms)
    {
        $search_terms = explode(" ", $input_search);
        if(count($search_terms) > 1)
        {
            array_unshift($search_terms, $input_search);
        }
    }

    private function getProductListing($inputs)
    {
        $productIds = [];
        if (isset($inputs['search'])) {
            $search_terms = [];
            $this->getSearchTerms($inputs['search'], $search_terms);
            foreach($search_terms as $searchIndex => $searchKeyword)
            {
                $productQuery = $this->product->newQuery()->whereIsActive(1)->where(function($q) use($searchKeyword){
                    $this->searchProductWithoutFuzzy($q, $searchKeyword);
                });
                $fetchProductIds = $productQuery->pluck('id')->toArray();
                $productIds = array_values(array_unique(array_merge($productIds, $fetchProductIds)));
            }
        }else{
            $productIds = $this->product->newQuery()->whereIsActive(1)->pluck('id')->toArray();
        }

        $productQuery = $this->product->newQuery()->whereIn('id', $productIds)->where(function($q){
                $q->orWhereHas('productMailBoxes')->orWhereHas('productMerchants');
            })
            ->where(function($q){
                $q->whereNull('published_at')->orWhere('published_at', '<=', Carbon::now()->format('Y-m-d'));
                $q->whereNull('expired_at')->orWhere('expired_at', '>', Carbon::now()->format('Y-m-d'));
            });

        if(!empty($inputs['show_buy_now']) || !empty($inputs['get_it_tomorrow'])  || isset($inputs['random_products']))
        {
            $productQuery->whereHas('productMailBoxes', function($q){
                $q->where('stock', '>', 0);
            });
            $productQuery->where('is_buy_now', 1);
        }

        if(!empty($inputs['menu_id']))
        {
            $productQuery->whereHas('product_menus', function($q) use($inputs){
                $q->where('menus.id', $inputs['menu_id']);
            });
        }

        if(!empty($inputs['tag_title']))
        {
            $productQuery->whereHas('product_tags', function($q) use($inputs){
                $q->whereIn('tags.title', $inputs['tag_title']);
            });
        }
        return $productQuery;
    }

    private function getItTomorrowCountForCustomer($productIds = null)
    {
        $productQuery = $this->product->newQuery()->whereIsActive(1);
        if($productIds)
        {
            $productQuery->whereIn('id', $productIds);
        }
        $productQuery->where(function($q){
                $q->orWhereHas('productMailBoxes')->orWhereHas('productMerchants');
            })
            ->where(function($q){
                $q->whereNull('published_at')->orWhere('published_at', '<=', Carbon::now()->format('Y-m-d'));
                $q->whereNull('expired_at')->orWhere('expired_at', '>', Carbon::now()->format('Y-m-d'));
            });

            $productQuery->whereHas('productMailBoxes', function($q){
                $q->where('stock', '>', 0);
            });
            $productQuery->where('is_buy_now', 1);
            return $productQuery->count();
    }

    private function getProductListingForAdmin($inputs)
    {
        $productIds = [];
        if (!empty($inputs['search'])) {
            $searchString = explode(" ", $inputs['search']);
            foreach($searchString as $searchKeyword)
            {
                $productQuery = $this->product->newQuery()->where(function($q) use($searchKeyword){
                    $this->searchProductWithoutFuzzy($q, $searchKeyword);
                });
                $fetchProductIds = $productQuery->pluck('id')->toArray();
                $productIds = array_values(array_unique(array_merge($productIds, $fetchProductIds)));
            }
        }else{
            $productIds = $this->product->newQuery()->pluck('id')->toArray();
        }

        $productQuery = $this->product->newQuery()->whereIn('id', $productIds);

        if(!empty($inputs['menu_id']))
        {
            $productQuery->whereHas('product_menus', function($q){
                $q->where('menus.id', request('menu_id'));
            });
        }

        if(!empty($inputs['show_buy_now']) || !empty($inputs['get_it_tomorrow']))
        {
            $productQuery->whereHas('productMailBoxes', function($q){
                $q->where('stock', '>', 0);
            });
            $productQuery->where('is_buy_now', 1);
        }

        if(!empty($inputs['tag_title']))
        {
            $productQuery->whereHas('product_tags', function($q){
                $q->whereIn('tags.title', request('tag_title'));
            });
        }

        if(!empty($inputs['top_filter']))
        {
            if($inputs['top_filter'] == DISABLED_FILTER)
            {
                $productQuery->whereIsActive(false);
            }elseif($inputs['top_filter'] == ACTIVE_FILTER)
            {
                $productQuery->whereIsActive(true);
            }elseif($inputs['top_filter'] == NO_MERCHANTS_FILTER)
            {
                $productQuery->whereDoesntHave('productMerchants');
            }elseif($inputs['top_filter'] == NOT_FOR_SALE_FILTER)
            {
                $productQuery->whereDoesntHave('productMailBoxes');
            }
        }

        return $productQuery;
    }

    private function getItTomorrowCountForAdmin($productIds = null)
    {
        $productQuery = $this->product->newQuery()->whereNotNull('id');
        if($productIds)
        {
            $productQuery->whereIn('id', $productIds);
        }
        $productQuery->whereHas('productMailBoxes', function($q){
            $q->where('stock', '>', 0);
        });
        $productQuery->where('is_buy_now', 1);
        return $productQuery->count();
    }

    private function isProductAllRelationShipTablesDeleted($productId)
    {
        if(ProductAttribute::whereProductId($productId)->count() > 0)
        {
            if(!ProductAttribute::whereProductId($productId)->delete())
            {
                return false;
            }
        }

        if(ProductImage::whereProductId($productId)->count() > 0)
        {
            if(!ProductImage::whereProductId($productId)->delete())
            {
                return false;
            }
        }

        if(ProductMenu::whereProductId($productId)->count() > 0)
        {
            if(!ProductMenu::whereProductId($productId)->delete())
            {
                return false;
            }
        }

        if(ProductTag::whereProductId($productId)->count() > 0)
        {
            if(!ProductTag::whereProductId($productId)->delete())
            {
                return false;
            }
        }

        if(ProductMerchant::whereProductId($productId)->count() > 0)
        {
            if(!ProductMerchant::whereProductId($productId)->delete())
            {
                return false;
            }
        }

        if(ProductMailBox::whereProductId($productId)->count() > 0)
        {
            if(!ProductMailBox::whereProductId($productId)->delete())
            {
                return false;
            }
        }

        return true;
    }


}
