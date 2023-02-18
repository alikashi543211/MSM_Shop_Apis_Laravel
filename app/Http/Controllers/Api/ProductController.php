<?php

namespace App\Http\Controllers\Api;

use App\Exports\ProductsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\ChangeStatusRequest;
use App\Http\Requests\Api\Product\CloneRequest;
use App\Http\Requests\Api\Product\DeleteAttributeRequest;
use App\Http\Requests\Api\Product\DeleteRequest;
use App\Http\Requests\Api\Product\DetailRequest;
use App\Http\Requests\Api\Product\FileImportRequest;
use App\Http\Requests\Api\Product\FilterListingRequest;
use App\Http\Requests\Api\Product\ListingRequest;
use App\Http\Requests\Api\Product\StoreRequest;
use App\Http\Requests\Api\Product\UpdateProductRequest;
use App\Http\Requests\Api\Product\UpdateRequest;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductMailBox;
use App\Models\ProductMenu;
use App\Models\ProductMerchant;
use App\Models\ProductTag;
use App\Models\Tag;
use App\Traits\Api\CategoryTrait;
use App\Traits\Api\ProductTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    use ProductTrait, CategoryTrait;
    private $product, $productAttribute, $productMenu, $productTag, $menu, $tag, $category, $productMailBox, $productMerchant;
    public function __construct()
    {
        $this->product = new Product();
        $this->productAttribute = new ProductAttribute();
        $this->productMenu = new ProductMenu();
        $this->productTag = new ProductTag();
        $this->menu = new Menu();
        $this->category = new Category();
        $this->tag = new Tag();
        $this->productMailBox = new ProductMailBox();
        $this->productMerchant = new ProductMerchant();
    }
    /**
     * Product Listing on Admin Side With Search Filters
     *
     * @param  ListingRequest
     * @return json
     */

    public function listing(ListingRequest $request)
    {
        $inputs = $request->all();
        $query = $this->getProductListingForAdmin($inputs);
        // With Data
        $products = $query->with(['productAttributes', 'product_menus', 'product_categories', 'productImages', 'productMerchants' => function($q){
                $q->with('merchant');
            }, 'productMailBoxes'])
            ->withCount(['productAttributes', 'product_menus', 'productImages', 'productMerchants', 'productMailBoxes'])
            ->paginate(PAGINATE)->toArray();
        // Custom Values
        foreach($products['data'] as $key => $product)
        {
            $products['data'][$key]['product_categories_count'] = $this->getProductCategoryCount($product['id']);
            $products['data'][$key]['product_tags'] = $this->getNatSortedProductTags($product['id']);
            $products['data'][$key]['product_tags_count'] = count($this->getNatSortedProductTags($product['id']));
        }
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $products);
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $product = $this->product->newInstance();
            $product->fill($inputs);
            $product->slug = getUniqueSlug($inputs['title']);
            $product->user_id = auth()->user()->id;
            if ($product->save()) {
                if(isset($inputs['product_attributes']))
                {
                    if(!$this->saveProductAttributes($inputs, $product->id))
                    {
                        DB::rollback();
                        return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                    }
                }
                DB::commit();
                return successDataResponse(GENERAL_SUCCESS_MESSAGE, $this->getProductDetail($product->id));
            }
            DB::rollback();
            return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function clone(CloneRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $productDetail = $this->getProductDetail($inputs['id']);
            $result = $this->cloneProduct($inputs);
            if($result[0])
            {
                $newProductId = $result[1];
                DB::commit();
                return successDataResponse(GENERAL_SUCCESS_MESSAGE, ['id' => $newProductId]);
            }
            DB::rollBack();
            return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function update(UpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $product = $this->product->newQuery()->whereId($inputs['id'])->first();
            $product->fill($inputs);
            $product->slug = getUniqueSlug($inputs['title']);
            $product->user_id = auth()->user()->id;
            if ($product->save()) {
                if(isset($inputs['product_attributes']))
                {
                    if(!$this->saveProductAttributes($inputs, $product->id))
                    {
                        DB::rollback();
                        return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                    }
                }
                DB::commit();
                return successDataResponse(GENERAL_UPDATED_MESSAGE, $this->getProductDetail($product->id));
            }
            DB::rollback();
            return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function delete(DeleteRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $product = $this->product->newQuery()->where('id', $inputs['id'])->first();
            if (!$product->delete()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if (!$this->isProductAllRelationShipTablesDeleted($inputs['id'])) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successResponse(GENERAL_DELETED_MESSAGE);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function changeStatus(ChangeStatusRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $product = $this->product->newQuery()->where('id', $inputs['id'])->first();
            if($product->is_active)
            {
                $product->is_active = 0;
            }else{
                $product->is_active = 1;
            }
            if (!$product->save()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if($product->expired_at < Carbon::now()->format('Y-m-d'))
            {
                $product->expired_at = null;
            }
            if($product->published_at < Carbon::now()->format('Y-m-d') && $product->is_active)
            {
                $product->published_at = Carbon::now()->format('Y-m-d');
            }
            if(!$product->save())
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successResponse(GENERAL_UPDATED_MESSAGE);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function changeBuyNow(ChangeStatusRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $product = $this->product->newQuery()->where('id', $inputs['id'])->first();
            if($product->is_buy_now)
            {
                $product->is_buy_now = 0;
            }else{
                $product->is_buy_now = 1;
            }
            if (!$product->save()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successResponse(GENERAL_UPDATED_MESSAGE);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function changeBuyingOptions(ChangeStatusRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $product = $this->product->newQuery()->where('id', $inputs['id'])->first();
            if($product->show_buying_options)
            {
                $product->show_buying_options = 0;
            }else{
                $product->show_buying_options = 1;
            }
            if (!$product->save()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successResponse(GENERAL_UPDATED_MESSAGE);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function detail(DetailRequest $request)
    {
        $inputs = $request->all();
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $this->getProductDetail($inputs['id']));
    }

    public function filterListing(FilterListingRequest $request)
    {
        $inputs = $request->all();
        $query = $this->category->newQuery()->has('category_products')->with('menu');
        $uniqueTagTitleList = [];
        $uniqueCatTitleList = [];
        $categories = [];
        $productIds = null;
        $getItTomorrowCount = $this->getItTomorrowCountForAdmin();

        if(!empty($inputs['search']) || !empty($inputs['menu_id']) || !empty($inputs['top_filter']) || !empty($inputs['tag_title']))
        {
            $productIds = $this->getProductListingForAdmin($inputs)->pluck('id')->toArray();
            $getItTomorrowCount = $this->getItTomorrowCountForAdmin($productIds);
            $products = $this->getProductListingForAdmin($inputs)->get();
            foreach($products as $item)
            {
                $uniqueCatTitleList = array_unique(array_merge($uniqueCatTitleList, $item->product_categories->pluck('title')->toArray()));
                $uniqueTagTitleList = array_unique(array_merge($uniqueTagTitleList, $item->product_tags->pluck('title')->toArray()));
            }
            $query->whereIn('title', $uniqueCatTitleList);
        }

        $uniqueCatTitleList = array_unique($query->pluck('title')->toArray());
        $key = 1;
        foreach($uniqueCatTitleList as $cat)
        {
            if($cat == 'Brands')
            {
                continue;
            }
            $categories[$key]['title'] = $cat;
            $tags = $this->getNatSortedCategoryTagsForMenu($cat, ((!empty($inputs['search']) || !empty($inputs['tag_title']) || !empty($inputs['menu_id'])) ? $uniqueTagTitleList : null));
            $categories[$key]['tags'] = $this->updateProductTagsCount($tags, $productIds);
            $key++;
        }

        $this->updateBrandsCategoryOnFirstIndex($uniqueCatTitleList, $categories, $productIds, $getItTomorrowCount);
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $categories);
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

    public function filterListingOld(FilterListingRequest $request)
    {
        $inputs = $request->all();
        $query = $this->category->newQuery()->has('category_products');
        $tagIds = [];
        $categoryIds = [];
        $is_menu_filter = false;

        if(!empty($inputs['menu_id']))
        {
            $is_menu_filter = true;
            $menu = $this->menu->whereId($inputs['menu_id'])->with(['products.product_categories', 'products.productTags'])->first();
            foreach($menu->products as $item)
            {
                $categoryIds = array_merge($categoryIds, $item->product_categories->pluck('id')->toArray());
                $tagIds = array_merge($tagIds, $item->productTags->pluck('tag_id')->toArray());
            }
            $query->whereIn('id', $categoryIds);
        }

        $categories = $query->get()->toArray();
        foreach($categories as $key => $cat)
        {
            $categories[$key]['tags'] = $this->getNatSortedCategoryTagsForMenuAdmin($cat['id'], (!empty($inputs['menu_id']) ? $tagIds : null));
        }

        return successDataResponse(GENERAL_FETCHED_MESSAGE, $categories);
    }

    public function deleteAttribute(DeleteAttributeRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $productAttribute = $this->productAttribute->newQuery()->whereId($inputs['id'])->first();
            if (!$productAttribute->delete()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successResponse(GENERAL_DELETED_MESSAGE);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function checkTotalPriceForCheckout(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();

            $totalPrice = 0;

            foreach($inputs['items'] AS $item)
            {
                $product = $this->product->newQuery()->whereId($item['id'])->first();
                if($product){
                    $mailbox = ProductMailBox::whereProductId($item['id'])->whereId($item['mailbox_id'])->first();
                    if($mailbox){
                        $totalPrice += $item['quantity'] * $mailbox->cost_after_discount;
                    }
                }
            }

            $data['total_price'] = $totalPrice;
            return successDataResponse(GENERAL_SUCCESS_MESSAGE, $data);

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function fileImport(FileImportRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $message = null;
            $result = null;
            if($request->hasFile('attachment'))
            {
                $import = new ProductsImport($result, $message);
                Excel::import($import, $request->attachment);
            }
            $responseImport = Session::get('responseImport');
            if($responseImport['result'] == false)
            {
                return errorResponse($responseImport['message'], ERROR_400);
            }
            DB::commit();
            return successResponse(GENERAL_SUCCESS_MESSAGE);

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function updateProduct(UpdateProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            foreach($inputs['product_mail_boxes'] as $mailBox)
            {
                $pMailBox = $this->productMailBox->newQuery()->whereId($mailBox['id'])->first();
                $pMailBox->landed_cost = $mailBox['landed_cost'];
                $pMailBox->discount = $mailBox['discount'];
                if(!$pMailBox->save())
                {
                    DB::rollback();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }
            }
            foreach($inputs['product_merchants'] as $productMerchant)
            {
                $pMerchant = $this->productMerchant->newQuery()->whereId($productMerchant['id'])->first();
                $pMerchant->estimated_landed_cost = $productMerchant['estimated_landed_cost'];
                $pMerchant->link = $productMerchant['link'];
                if(!$pMerchant->save())
                {
                    DB::rollback();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }
            }

            DB::commit();
            return successDataResponse(GENERAL_UPDATED_MESSAGE, $this->getProductDetail($inputs['id']));

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function fileExport(Request $request)
    {
        $inputs = $request->all();
        $query = $this->getProductListingForAdmin($inputs);
        // With Data
        $products = $query->with(['productAttributes', 'product_menus', 'product_categories', 'productImages', 'productMerchants' => function($q){
                $q->with('merchant');
            }, 'productMailBoxes'])
            ->withCount(['productAttributes', 'product_menus', 'productImages', 'productMerchants', 'productMailBoxes'])
            ->get();

        $name = $this->getExcelFileName('products');
        $export = new ProductsExport($products);
        Excel::store($export, $name, 'public');
        $link = url(Storage::url($name));
        return successDataResponse(GENERAL_FETCHED_MESSAGE, ['link' => $link]);
    }

}
