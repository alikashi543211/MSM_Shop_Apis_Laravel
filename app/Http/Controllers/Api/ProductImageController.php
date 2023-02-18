<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductImage\DeleteRequest;
use App\Http\Requests\Api\ProductImage\ListingRequest;
use App\Http\Requests\Api\ProductImage\RemoveImageRequest;
use App\Http\Requests\Api\ProductImage\StoreRequest;
use App\Http\Requests\Api\ProductImage\UpdateRequest;
use App\Http\Requests\Api\ProductImage\UpdateSortingRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Traits\Api\ProductImageTrait;
use App\Traits\Api\ProductTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductImageController extends Controller
{
    use ProductImageTrait, ProductTrait;
    private $product, $productImage;

    public function __construct()
    {
        $this->product = new Product();
        $this->productImage = new ProductImage();
    }

    public function listing(ListingRequest $request)
    {
        $inputs = $request->all();
        $productImages = $this->productImage->newQuery()->whereProductId($inputs['product_id'])->orderBy('sort_number', 'ASC');
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $productImages->paginate(PAGINATE));
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $productImage = $this->productImage->newInstance();
            $productImage->fill($inputs);
            $productImage->sort_number = $this->getNextProductImageSortNumber($inputs['product_id']);
            if($request->hasFile('image'))
            {
                $productImage->image = uploadFile($inputs['image'], 'uploads/product-images', 'product-image');

            }
            if ($productImage->save()) {
                DB::commit();
                return successDataResponse(GENERAL_SUCCESS_MESSAGE, $productImage->fresh());
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

    public function update(UpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $productImage = $this->productImage->newQuery()->whereId($inputs['id'])->first();
            $oldProductId = $productImage->product_id;
            $productImage->fill($inputs);
            if($request->hasFile('image'))
            {
                $this->deleteImage($productImage->image_path);
                $productImage->image = uploadFile($inputs['image'], 'uploads/product-images', 'product-image');

            }
            if ($productImage->save()) {
                DB::commit();
                return successDataResponse(GENERAL_SUCCESS_MESSAGE, $productImage->fresh());
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
            $productImage = $this->productImage->newQuery()->where('id', $inputs['id'])->first();
            $this->deleteImage($productImage->image_path);
            if (!$productImage->delete()) {
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

    public function updateSorting(UpdateSortingRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if(!$this->validateSizeOfIds($inputs)[0])
            {
                DB::rollback();
                return errorResponse("The ids must contain ". $this->validateSizeOfIds($inputs)[1] ." items.", ERROR_400);
            }
            foreach($inputs['ids'] AS $key => $value){

                $productImage = $this->productImage->whereId($value)->first();
                $productImage->sort_number = $key + 1;
                if (!$productImage->save()) {
                    DB::rollback();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }

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

    public function removeImage(RemoveImageRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $productImage = $this->productImage->newQuery()->whereId( $inputs['id'])->first();
            $this->deleteImage($productImage->image_path);
            $productImage->image = null;
            if (!$productImage->save()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successDataResponse(GENERAL_UPDATED_MESSAGE, $productImage->fresh());
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

}
