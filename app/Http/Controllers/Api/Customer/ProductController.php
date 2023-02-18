<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\Product\AllFilterRequest;
use App\Http\Requests\Api\Customer\Product\DetailRequest;
use App\Http\Requests\Api\Customer\Product\FilterListingRequest;
use App\Http\Requests\Api\Customer\Product\ListingRequest;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductMailBox;
use App\Models\ProductMenu;
use App\Models\ProductTag;
use App\Models\Tag;
use App\Models\User;
use App\Traits\Api\CategoryTrait;
use App\Traits\Api\ProductTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use ProductTrait, CategoryTrait;
    private $product, $productAttribute, $productMenu, $productTag, $menu, $tag, $category;
    public function __construct()
    {
        $this->product = new Product();
        $this->productAttribute = new ProductAttribute();
        $this->productMenu = new ProductMenu();
        $this->productTag = new ProductTag();
        $this->menu = new Menu();
        $this->category = new Category();
        $this->tag = new Tag();
    }

    /**
     * Product Listing on Customer Side With Search Filters
     *
     * @param  ListingRequest
     * @return json
     */
    public function listing(ListingRequest $request)
    {
        $inputs = $request->all();
        $raw = null;
        $searchTermsCounts = [];
        $sortedProducts = null;
        $random_inputs = ["random_products" => "random products"];
        $random_products = [];
        $query = $this->getProductListing($inputs);

        // Order By Stock Descending
        $query->addSelect(['balance' => ProductMailBox::selectRaw('sum(stock) as total')
                ->whereColumn('product_id', 'products.id')
                ->groupBy('product_id')
        ]);

        $random_query = $this->getProductListing($random_inputs);
        $this->getRawQueryForTopSearchResultsInternet($inputs, $raw);

        $query = $query->with(['productAttributes', 'product_menus', 'product_categories', 'productImages', 'productMerchants' => function($q){
                $q->with('merchant');
            }, 'productMailBoxes'])
            ->withCount(['productAttributes', 'product_menus', 'productImages', 'productMerchants', 'productMailBoxes']);

        if($raw)
        {
            $query = $query->addSelect(DB::raw($raw));
            $products = $query->orderBy('weight', 'DESC')->paginate(CUSTOMER_PRODUCT_PAGINATE)->toArray();
        }else{
            $products = $query->orderBy('balance', 'DESC')->paginate(CUSTOMER_PRODUCT_PAGINATE)->toArray();
        }

        $random_products = $random_query->with(['productAttributes', 'product_menus', 'product_categories', 'productImages', 'productMerchants' => function($q){
                $q->with('merchant');
            }, 'productMailBoxes'])
            ->withCount(['productAttributes', 'product_menus', 'productImages', 'productMerchants', 'productMailBoxes'])
            ->where('is_active', true)
            ->where('is_buy_now', true)
            ->inRandomOrder()
            ->limit(RANDOM_PRODUCT_LIMIT)
            ->get()->toArray();

        foreach($products['data'] as $key => $product)
        {
            // dd($product)
            $products['data'][$key]['product_categories_count'] = $this->getProductCategoryCount($product['id']);
            $products['data'][$key]['product_tags'] = $this->getNatSortedProductTags($product['id']);
            $products['data'][$key]['product_tags_count'] = count($this->getNatSortedProductTags($product['id']));
        }

        $data = [
            'products' => $products,
            'random_products' => $random_products
        ];
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $data);
    }

    private function sortProductsBySearch($products, &$sortedProducts, $searchTermsCounts)
    {
        $sortedKey = 0;
        $sortedProducts = $products;
        if( count($searchTermsCounts) > 0 )
        {
            $sortedProducts['data'] = null;
            foreach($searchTermsCounts as $searchTerm)
            {
                foreach($searchTerm['search_product_ids'] as $searchTermProductId)
                {
                    foreach($products['data'] as $key => $product)
                    {
                        if($product['id'] == $searchTermProductId)
                        {
                            $sortedProducts['data'][$sortedKey] = $product;
                            $sortedKey++;
                        }
                    }
                }

            }
        }

    }

    private function getRawQueryForTopSearchResultsInternet($inputs, &$raw)
    {
        if(isset($inputs['search']))
        {
            $search_terms = [];
            $this->getSearchTerms($inputs['search'], $search_terms);
            $raw = "(";
            foreach($search_terms as $key) {
                $raw .= "(products.title LIKE '%".$key."%')+";
            }
            $raw = substr($raw, 0, -1); // Remove the last "+"
            $raw .= ") as weight";
        }
    }

    private function getSearchTermCounts($inputs, &$searchTermsCounts, $productIds)
    {
        if(isset($inputs['search']))
        {
            $search_terms = [];
            $this->getSearchTerms($inputs['search'], $search_terms);
            foreach($search_terms as $searchIndex => $searchKeyword)
            {

                $searchTermsCounts[$searchIndex]['search_title'] = $searchKeyword;
                $filterProductIds = $this->product->newQuery()->whereIn('id', $productIds)->where('title', 'LIKE', '%'.$searchKeyword.'%')->pluck('id')->toArray();
                $searchTermsCounts[$searchIndex]['search_weight'] = count($filterProductIds);
                $searchTermsCounts[$searchIndex]['search_product_ids'] = $filterProductIds;
            }
            $searchTermsCounts = collect($searchTermsCounts)->sortByDesc('search_weight', SORT_REGULAR, true)->toArray();
        }
    }

    public function detail(DetailRequest $request)
    {
        $inputs = $request->all();

        return successDataResponse(GENERAL_FETCHED_MESSAGE, $this->getProductDetail($inputs['id']));
    }

    /**
     * Dynamic Left Filter Menu On Customer Side
     *
     * @param  FilterListingRequest
     * @return json
     */
    public function filterListing(FilterListingRequest $request)
    {
        $inputs = $request->all();
        $query = $this->category->newQuery()->has('activeCategoryProducts')->with('menu');
        $uniqueTagTitleList = [];
        $uniqueCatTitleList = [];
        $categories = [];
        $productIds = null;
        $getItTomorrowCount = $this->getItTomorrowCountForCustomer();

        // When User Search with keyword or by filter tag or by Menu or by Show Buy Now Toggle
        if(!empty($inputs['search']) || !empty($inputs['tag_title']) || !empty($inputs['menu_id']) || !empty($inputs['show_buy_now']))
        {

            $productIds = $this->getProductListing($inputs)->pluck('id')->toArray();
            $getItTomorrowCount = $this->getItTomorrowCountForCustomer($productIds);
            $products = $this->getProductListing($inputs)->get();
            foreach($products as $item)
            {
                $uniqueCatTitleList = array_unique(array_merge($uniqueCatTitleList, $item->product_categories->pluck('title')->toArray()));
                $uniqueTagTitleList = array_unique(array_merge($uniqueTagTitleList, $item->product_tags->pluck('title')->toArray()));
            }
            $query->whereIn('title', $uniqueCatTitleList);
        }

        // Plucking The Unique Category Names
        $uniqueCatTitleList = array_unique($query->pluck('title')->toArray());
        $key = 1;
        foreach($uniqueCatTitleList as $cat)
        {
            if($cat == 'Brands')
            {
                continue;
            }
            $categories[$key]['title'] = $cat;
            $tags = $this->getNatSortedCategoryTagsForMenu($cat, ((!empty($inputs['search']) || !empty($inputs['tag_title']) || !empty($inputs['menu_id']) || !empty($inputs['show_buy_now'])) ? $uniqueTagTitleList : null));
            $categories[$key]['tags'] = $this->updateProductTagsCount($tags, $productIds);
            $key++;
        }
        // $this->updateSortingOfFilter($categories);
        $this->updateBrandsCategoryOnFirstIndex($uniqueCatTitleList, $categories, $productIds, $getItTomorrowCount);
        return successDataResponse(GENERAL_FETCHED_MESSAGE, array_values($categories));
    }

    private function updateBrandsCategoryOnFirstIndex($uniqueCatTitleList, &$categories, $productIds, $getItTomorrowCount)
    {
        // Brands Category On First Index
        if(in_array('Brands', $uniqueCatTitleList))
        {
            $zeroIndex = 0;
            $cat = 'Brands';
            $categories[$zeroIndex]['get_tomorrow_count'] = $getItTomorrowCount;
            $categories[$zeroIndex]['title'] = $cat;
            $tags = $this->getNatSortedCategoryTagsForMenu($cat, ((!empty($inputs['search']) || !empty($inputs['tag_title']) || !empty($inputs['menu_id']) || !empty($inputs['show_buy_now'])) ? $uniqueTagTitleList : null));
            $categories[$zeroIndex]['tags'] = $this->updateProductTagsCount($tags, $productIds);
            ksort($categories);
        }
    }
    private function updateSortingOfFilter($categories)
    {
        // dd($categories);
        return null;
    }

    private function updateProductTagsCount($tags, $productIds)
    {
        $data = [];
        $index = 0;
        foreach($tags as $tagKey => $tagValue)
        {
            $product_tags_count = $this->getProductTagsCount($tagValue, $productIds);
            if($product_tags_count == 0)
            {
                continue;
            }
            $data[$index]['title'] = $tagValue;
            $data[$index]['product_tags_count'] = $product_tags_count;
            $index++;
        }
        return $data;
    }

    private function getProductTagsCount($tagValue, $productIds)
    {
        $query = $this->product->newQuery()->whereHas('product_tags', function($q) use($tagValue){
            $q->whereTitle($tagValue);
        });
        if($productIds)
        {
            $query->whereIn('id', $productIds);
        }
        return $query->count();
    }

    public function allFilter(AllFilterRequest $request)
    {
        $inputs = $request->all();
        $menuList = $this->menu->newQuery()->has('active_products')->get();
        $menuFilter = [];
        $menuIndex = 0;
        $this->getCustomerFilter($inputs, $menuFilter, $menuIndex, 'In stock now', null, null);
        foreach($menuList as $menu)
        {
            $this->getCustomerFilter($inputs, $menuFilter, $menuIndex, $menu->title, $menu->slug, $menu->id);
        }

        return successDataResponse(GENERAL_FETCHED_MESSAGE, array_values($menuFilter));
    }

    private function getCustomerFilter(&$inputs, &$menuFilter, &$menuIndex, $menuTitle, $menuSlug, $menuId = null)
    {
        if($menuId)
        {
            $inputs['menu_id'] = $menuId;
        }
        $query = $this->category->newQuery()->has('activeCategoryProducts')->with('menu');
        $uniqueTagTitleList = [];
        $uniqueCatTitleList = [];
        $categories = [];
        $productIds = null;
        $getItTomorrowCount = $this->getItTomorrowCountForCustomer();

        // When User Search with keyword or by filter tag or by Menu or by Show Buy Now Toggle
        if(!empty($inputs['search']) || !empty($inputs['tag_title']) || !empty($inputs['menu_id']) || !empty($inputs['show_buy_now']))
        {

            $productIds = $this->getProductListing($inputs)->pluck('id')->toArray();
            $getItTomorrowCount = $this->getItTomorrowCountForCustomer($productIds);
            $products = $this->getProductListing($inputs)->get();
            foreach($products as $item)
            {
                $uniqueCatTitleList = array_unique(array_merge($uniqueCatTitleList, $item->product_categories->pluck('title')->toArray()));
                $uniqueTagTitleList = array_unique(array_merge($uniqueTagTitleList, $item->product_tags->pluck('title')->toArray()));
            }
            $query->whereIn('title', $uniqueCatTitleList);
        }

        // Plucking The Unique Category Names
        $uniqueCatTitleList = array_unique($query->pluck('title')->toArray());
        $key = 1;
        foreach($uniqueCatTitleList as $cat)
        {
            if($cat == 'Brands')
            {
                continue;
            }
            $categories[$key]['title'] = $cat;
            $tags = $this->getNatSortedCategoryTagsForMenu($cat, ((!empty($inputs['search']) || !empty($inputs['tag_title']) || !empty($inputs['menu_id']) || !empty($inputs['show_buy_now'])) ? $uniqueTagTitleList : null));
            $categories[$key]['tags'] = $this->updateProductTagsCount($tags, $productIds);
            $key++;
        }
        // $this->updateSortingOfFilter($categories);
        $this->updateBrandsCategoryOnFirstIndex($uniqueCatTitleList, $categories, $productIds, $getItTomorrowCount);
        // dd($categories);
        $menuFilter[$menuIndex]['menu_title'] = $menuTitle;
        $menuFilter[$menuIndex]['menu_slug'] = $menuSlug;
        $menuFilter[$menuIndex]['menu_filter'] = array_values($categories);
        $menuIndex++;
    }
}
