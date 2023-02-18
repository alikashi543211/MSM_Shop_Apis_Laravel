<?php
namespace App\Traits\Api;

use App\Models\Cart;
use App\Models\ProductMailBox;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait ShoppingCartTimeCroneTrait
{

    private function expireCartItemForCroneTrait($cartItem)
    {
        // Update Product  MailBox Stock
        $productMailBox = ProductMailBox::whereId($cartItem->mailbox['id'])->first();
        $productMailBox->stock = $productMailBox->stock + $cartItem->quantity;
        $productMailBox->save();
        // Update Cart Mailbox Json For Stock Value Update
        $data = [];
        foreach($cartItem->mailbox as $key => $value)
        {
            if($key == 'stock')
            {
                $data[$key] = (integer) $productMailBox->stock;
            }else{
                $data[$key] = $value;
            }
        }
        $cartItem->mailbox = $data;
        if(!$cartItem->save())
        {
            return false;
        }
        return true;
    }

    private function isStockPreUpdatedWhenUserCartDelete($userId)
    {
        if($this->cart->newQuery()->whereUserId($userId)->count() > 0)
        {
            $cartList = $this->cart->newQuery()->whereUserId($userId)->where('reserved_time', '!=', CART_ITEM_TIME_OUT)->get();
            foreach($cartList as $key => $cartItem)
            {

                if(!$this->isStockPreUpdated($cartItem))
                {
                    return false;
                }
            }
            if(!$this->cart->newQuery()->whereUserId($userId)->delete())
            {
                return false;
            }
        }

        return true;
    }

    private function isStockPreUpdated($cartItem)
    {
        $productMailBox = ProductMailBox::whereId($cartItem->mailbox['id'])->first();
        if(!$productMailBox)
        {
            return false;
        }
        if($productMailBox)
        {
            $productMailBox->stock = $productMailBox->stock + $cartItem->quantity;
            $productMailBox->save();
        }

        return true;
    }

    private function expireCartItemCrone()
    {
        $carts = $this->cart->newQuery()->where('reserved_time', '!=', CART_ITEM_TIME_OUT)->get();
        if(count($carts) > 0)
        {
            foreach($carts as $key => $cartItem)
            {
                if($cartItem->expired_at)
                {
                    $reservedTime = getReservedTime(Carbon::now()->format('Y-m-d H:i:s'), $cartItem->expired_at);
                    if($reservedTime == CART_ITEM_TIME_OUT)
                    {
                        if(!$this->expireCartItemForCroneTrait($cartItem))
                        {
                            return false;
                        }
                        $cartItem->reserved_time = CART_ITEM_TIME_OUT;
                        if(!$cartItem->save())
                        {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    private function deleteExpiredCartCrone()
    {

        $userIds = $this->cart->newQuery()->select('user_id')->distinct()->pluck('user_id')->toArray();
        if(count($userIds) > 0)
        {
            foreach($userIds as $key => $userId)
            {
                $cartItem = $this->cart->newQuery()->whereUserId($userId)->orderBy('cart_expired_at', 'DESC')->first();
                if($cartItem)
                {
                    if($cartItem->cart_expired_at)
                    {
                        $reservedTime = getReservedTime(Carbon::now()->format('Y-m-d H:i:s'), $cartItem->cart_expired_at);
                        if($reservedTime == CART_ITEM_TIME_OUT)
                        {
                            if(!$this->isStockPreUpdatedWhenUserCartDelete($userId))
                            {
                                return false;
                            }

                        }
                    }
                }
            }
        }

        return true;
    }

    private function updateReservedTimeForCartItemCrone()
    {
        $carts = $this->cart->newQuery()->get();
        foreach($carts as $key => $cartItem)
        {
            if($cartItem->cart_at && $cartItem->expired_at)
            {
                $cartItem->reserved_time = getReservedTime(Carbon::now()->format('Y-m-d H:i:s'), $cartItem->expired_at);
                if(!$cartItem->save())
                {
                    return false;
                }
            }

        }
        return true;
    }


}
