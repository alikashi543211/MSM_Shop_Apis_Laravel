<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductPricing\DeleteMailBoxRequest;
use App\Http\Requests\Api\ProductPricing\DeleteMerchantRequest;
use App\Http\Requests\Api\ProductPricing\StoreRequest;
use App\Http\Requests\Api\ProductPricing\UpdateDiscountTypeRequest;
use App\Http\Requests\Api\ProductPricing\UpdateMerchantSortingRequest;
use App\Models\Cart;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductMailBox;
use App\Models\ProductMerchant;
use App\Models\ProductTag;
use App\Traits\Api\ProductPricingTrait;
use App\Traits\Api\ProductTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductPricingController extends Controller
{
    use ProductPricingTrait, ProductTrait;
    private $product, $productMailbox, $productMerchant, $merchant, $productTag, $cart;

    public function __construct()
    {
        $this->product = new Product();
        $this->cart = new Cart();
        $this->merchant = new Merchant();
        $this->productTag = new ProductTag();
        $this->productMailbox = new ProductMailBox();
        $this->productMerchant = new ProductMerchant();
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if(!$this->saveProductMailBoxes($inputs))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->saveProductMerchants($inputs))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successDataResponse(GENERAL_SUCCESS_MESSAGE, $this->getProductMailBoxesAndMerchants($inputs['product_id']));

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function deleteMerchant(DeleteMerchantRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $productMerchant = $this->productMerchant->whereId($inputs['id'])->first();
            if(!$productMerchant->delete())
            {
                DB::rollBack();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_500);
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

    public function deleteMailBox(DeleteMailBoxRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $productMailbox = $this->productMailbox->whereId($inputs['id'])->first();
            if(!$this->isCartDeletedRelatedToMailbox($inputs['id']))
            {
                DB::rollBack();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_500);
            }
            if(!$productMailbox->delete())
            {
                DB::rollBack();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_500);
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

    public function updateMerchantSorting(UpdateMerchantSortingRequest $request)
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

                $productMerchant = $this->productMerchant->whereId($value)->first();
                $productMerchant->sort_number = $key + 1;
                if (!$productMerchant->save()) {
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

    public function merchantListing(Request $request)
    {
        $inputs = $request->all();
        $query = $this->merchant->newQuery()->select(['id', 'name']);
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['name', 'country', 'region', 'city', 'full_address', 'post_code', 'address', 'status']);
            });
        }
        $merchants = $query->get()->toArray();
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $merchants);
    }

    public function updateDiscountType(UpdateDiscountTypeRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $productMailbox = $this->productMailbox->newQuery()->where('id', $inputs['id'])->first();
            $productMailbox->discount_type = $inputs['discount_type'];
            if (!$productMailbox->save()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isSavedCostAfterDiscount($productMailbox))
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

}
