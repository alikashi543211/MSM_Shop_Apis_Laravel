<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductCatalogue\DeleteProductMenuRequest;
use App\Http\Requests\Api\ProductCatalogue\DeleteProductTagRequest;
use App\Http\Requests\Api\ProductCatalogue\StoreProductMenuRequest;
use App\Http\Requests\Api\ProductCatalogue\StoreProductTagRequest;
use App\Http\Requests\Api\ProductCatalogue\StoreRequest;
use App\Models\Product;
use App\Models\ProductMenu;
use App\Models\ProductTag;
use App\Traits\Api\ProductCatalogueTrait;
use App\Traits\Api\ProductTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCatalogueController extends Controller
{
    use ProductCatalogueTrait, ProductTrait;
    private $product, $productMenu, $productTag;

    public function __construct()
    {
        $this->product = new Product();
        $this->productMenu = new ProductMenu();
        $this->productTag = new ProductTag();
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if (!$this->saveMenuAndTags($inputs)) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successDataResponse(GENERAL_SUCCESS_MESSAGE, $this->getProductDetail($inputs['product_id']));

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function storeTag(StoreProductTagRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            foreach($inputs['category_tags'] as $categoryTag)
            {
                $productTag = $this->productTag->newInstance();
                $productTag->product_id = $inputs['product_id'];
                $productTag->category_id = $categoryTag['category_id'];
                $productTag->tag_id = $categoryTag['tag_id'];
                if(!$productTag->save())
                {
                    DB::rollback();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }
            }
            DB::commit();
            return successDataResponse(GENERAL_SUCCESS_MESSAGE, $this->getProductDetail($inputs['product_id']));

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function storeMenu(StoreProductMenuRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if($this->productMenu->whereProductId($inputs['product_id'])->whereMenuId($inputs['menu_id'])->first())
            {
                DB::rollback();
                return errorResponse(MENU_ALREADY_EXISTS_IN_PRODUCT, ERROR_400);
            }
            $productMenu = $this->productMenu->newInstance();
            $productMenu->menu_id = $inputs['menu_id'];
            $productMenu->product_id = $inputs['product_id'];
            if(!$productMenu->save())
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successDataResponse(GENERAL_SUCCESS_MESSAGE, $this->getProductDetail($inputs['product_id']));

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function deleteProductMenu(DeleteProductMenuRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $productMenu = $this->productMenu->newQuery()->whereId($inputs['id'])->first();
            $categoryIds = $productMenu->menu->categories()->pluck('id')->toArray();
            $productId = $productMenu->product_id;
            if (!$productMenu->delete()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if( $this->productTag->newQuery()->whereProductId($productId)->whereIn('category_id', $categoryIds)->count() > 0 )
            {
                $this->productTag->newQuery()->whereProductId($productId)->whereIn('category_id', $categoryIds)->delete();
            }
            DB::commit();
            return successDataResponse(GENERAL_DELETED_MESSAGE, $this->getProductDetail($productId));
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }
    public function deleteProductTag(DeleteProductTagRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $productTag = $this->productTag->newQuery()->whereId($inputs['id'])->first();
            $productId = $productTag->product_id;
            if (!$productTag->delete()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successDataResponse(GENERAL_DELETED_MESSAGE, $this->getProductDetail($productId));
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }
}
