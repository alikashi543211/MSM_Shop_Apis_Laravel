<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Cart\DeleteRequest;
use App\Http\Requests\Api\Cart\ListingRequest;
use App\Models\Cart;
use App\Models\ProductMailBox;
use App\Models\User;
use App\Traits\Api\CartTrait;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    use CartTrait;
    private $cart, $user, $productMailbox;
    public function __construct()
    {
        $this->cart = new Cart();
        $this->user = new User();
        $this->productMailbox = new ProductMailBox();
    }

    public function userListing(Request $request)
    {
        try {
            DB::beginTransaction();

            $inputs = $request->all();
            $query = $this->cart->newQuery()->select([
                'user_id', 'email', 'first_name', 'last_name', 'us_express_number'
            ]);

            if (!empty($inputs['search'])) {
                $query->where(function ($q) use ($inputs) {
                    searchTable($q, $inputs['search'], ['title', 'description', 'image', 'mailbox', 'price', 'quantity', 'slug', 'stock', 'email', 'first_name', 'last_name', 'us_express_number', 'cart_at', 'reserved_time', 'expired_at', 'cart_at']);
                });
            }

            if(!empty($inputs['user_id']))
            {
                $query->whereUserId($inputs['user_id']);
            }

            $data = $query->distinct()->paginate(PAGINATE);
            foreach($data as $key => $item)
            {
                $data[$key]['created_at'] = $this->getUserCartCreatedAt($item->user_id);
                $data[$key]['expired_at'] = $this->getUserCartExpiredAt($item->user_id);
                $data[$key]['cart_at'] = $this->getLastUpdatedAtCartItem($item->user_id);
                $data[$key]['items'] = $this->getUserCartDetailForAdmin($item->user_id);
            }

            DB::commit();
            return successDataResponse(GENERAL_FETCHED_MESSAGE, $data);

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }

    }

    public function delete( DeleteRequest $request)
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
