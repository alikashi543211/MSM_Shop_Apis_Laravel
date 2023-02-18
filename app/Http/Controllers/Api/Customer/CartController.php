<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\Cart\CheckoutRequest;
use App\Http\Requests\Api\Customer\Cart\CheckoutUserRequest;
use App\Http\Requests\Api\Customer\Cart\DeleteRequest;
use App\Http\Requests\Api\Customer\Cart\DeleteUserCartRequest;
use App\Http\Requests\Api\Customer\Cart\ListingRequest;
use App\Http\Requests\Api\Customer\Cart\StoreRequest;
use App\Http\Requests\Api\Customer\Cart\UpdateCartItemRequest;
use App\Http\Requests\Api\Customer\Cart\UpdateRequest;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductMailBox;
use App\Models\Setting;
use App\Traits\Api\CartTrait;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    use CartTrait;
    private $cart, $product, $productMailbox;
    public function __construct()
    {
        $this->cart = new Cart();
        $this->product = new Product();
        $this->productMailbox = new ProductMailBox();
    }

    public function listing(ListingRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if(!$this->updateReservedTime($inputs['user_id']))
            {
                DB::rollBack();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successDataResponse(GENERAL_FETCHED_MESSAGE, $this->getUserCartDetail($inputs));
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }

    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();

            if(!$this->isQuantityAvailable($inputs))
            {
                DB::rollback();
                return errorResponse(QUANTITY_NOT_AVAILABLE, ERROR_400);
            }
            $cartItem = $this->cart->newInstance();
            $cartItem->fill($inputs['item']);
            $cartItem->fill($inputs);
            if(!$cartItem->save())
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isValidatedPriceSaved($cartItem))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isCartAtExpiredAtReservedTimeUpdated($cartItem->user_id))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isUserCartExpiredAtUpdated($cartItem->user_id))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isStockUpdated($inputs['item']['mailbox']['id'], $inputs['item']['quantity'], $cartItem))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successDataResponse(GENERAL_SUCCESS_MESSAGE, $this->getUserCartDetail($inputs));

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function updateCartItem(UpdateCartItemRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $cartItem = $this->cart->newQuery()->whereId($inputs['item']['id'])->first();
            $inputs['user_id'] = $cartItem->user_id;
            $timeOutMinusFromStock = false;
            if($cartItem->reserved_time == CART_ITEM_TIME_OUT)
            {
                $timeOutMinusFromStock = true;
                $quantity = $inputs['item']['quantity'];
            }else{
                $quantity = 1;
            }
            if(!$this->isStockAvailableInProductMailBox($cartItem, $quantity, IS_QUANTITY_INCREASED_TRUE, $timeOutMinusFromStock))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            $cartItem->fill($inputs['item']);
            if(!$cartItem->save())
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isValidatedPriceSaved($cartItem))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isCartAtExpiredAtReservedTimeUpdated($cartItem->user_id, $cartItem->id))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isUserCartExpiredAtUpdated($cartItem->user_id))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isStockIncOrDec($cartItem->mailbox['id'], $quantity, $cartItem, IS_QUANTITY_INCREASED_TRUE, $timeOutMinusFromStock))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successDataResponse(GENERAL_SUCCESS_MESSAGE, $this->getUserCartDetail($inputs));

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
            $cartItem = $this->cart->newQuery()->whereId($inputs['id'])->first();
            $inputs['user_id'] = $cartItem->user_id;
            $timeOutMinusFromStock = false;
            if($cartItem->reserved_time == CART_ITEM_TIME_OUT)
            {
                $timeOutMinusFromStock = true;
                $quantity = $inputs['quantity'];
            }else{
                $quantity = 1;
            }
            if(!$this->isStockAvailableInProductMailBox($cartItem, $quantity, $inputs['is_quantity_increased'], $timeOutMinusFromStock))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            $cartItem->fill($inputs);
            if (!$cartItem->save()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isValidatedPriceSaved($cartItem))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isCartAtExpiredAtReservedTimeUpdated($cartItem->user_id, $cartItem->id))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isUserCartExpiredAtUpdated($cartItem->user_id))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }

            if(!$this->isStockIncOrDec($cartItem->mailbox['id'], $quantity, $cartItem, $inputs['is_quantity_increased'], $timeOutMinusFromStock))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }

            DB::commit();
            return successDataResponse(GENERAL_SUCCESS_MESSAGE, $this->getUserCartDetail($inputs));

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
            $cartItem = $this->cart->newQuery()->whereId($inputs['id'])->first();
            $inputs['user_id'] = $cartItem->user_id;
            // cart item ka timeout ho chuka he to stock dobara update nai karwaen ge.
            if(!$this->isStockPreUpdated($cartItem))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            $userId = $cartItem->user_id;
            $inputs['user_id'] = $userId;
            if(!$cartItem->delete())
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isCartAtExpiredAtReservedTimeUpdated($userId))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isUserCartExpiredAtUpdated($userId))
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successDataResponse(GENERAL_DELETED_MESSAGE, $this->getUserCartDetail($inputs));

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function deleteUserCart(DeleteUserCartRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if($this->cart->newQuery()->whereUserId($inputs['user_id'])->count() > 0)
            {
                if(!$this->isStockPreUpdatedWhenUserCartDelete($inputs['user_id']))
                {
                    DB::rollback();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }
                if(!$this->cart->newQuery()->whereUserId($inputs['user_id'])->delete())
                {
                    DB::rollback();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }
            }
            DB::commit();
            return successDataResponse(GENERAL_DELETED_MESSAGE, $this->getUserCartDetail($inputs));

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function checkout(CheckoutRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if($this->cart->newQuery()->whereUserId($inputs['user_id'])->count() > 0)
            {
                if(!$this->isCartAtExpiredAtReservedTimeUpdated($inputs['user_id']))
                {
                    DB::rollback();
                    return errorResponse(GENERAL_SUCCESS_MESSAGE, ERROR_400);
                }
                if(!$this->isUserCartExpiredAtUpdated($inputs['user_id']))
                {
                    DB::rollback();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }
            }
            DB::commit();
            return successDataResponse(GENERAL_DELETED_MESSAGE, $this->getUserCartDetail($inputs));

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function checkoutUser(CheckoutUserRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if($this->cart->newQuery()->whereUserId($inputs['user_id'])->count() > 0)
            {
                if(!$this->cart->newQuery()->whereUserId($inputs['user_id'])->delete())
                {
                    DB::rollback();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }
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

}
