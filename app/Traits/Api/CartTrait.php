<?php
namespace App\Traits\Api;

use App\Models\Setting;
use Carbon\Carbon;

trait CartTrait
{
    private function isCartSaved($inputs)
    {
        if($this->cart->newQuery()->whereUserId($inputs['user_id'])->count() > 0)
        {
            $this->cart->newQuery()->whereUserId($inputs['user_id'])->delete();
        }
        foreach($inputs['items'] as $item)
        {
            $cart = $this->cart->newInstance();
            $cart->fill($item);
            $cart->fill($inputs);
            $cart->cart_at = Carbon::now();
            if(!$cart->save())
            {
                return false;
            }
        }
        return true;

    }

    private function expireCartItemForCustomer($cartItem)
    {
        // Update Product  MailBox Stock
        $productMailBox = $this->productMailbox->newQuery()->whereId($cartItem->mailbox['id'])->first();
        if(!$productMailBox)
        {
            return false;
        }
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

    private function updateReservedTime($userId)
    {
        $carts = $this->cart->newQuery()->where('reserved_time', '!=' ,CART_ITEM_TIME_OUT)->whereUserId($userId)->get();
        if(count($carts) > 0)
        {
            foreach($carts as $key => $cartItem)
            {
                if($cartItem->cart_at && $cartItem->expired_at)
                {
                    $reservedTime = getReservedTime(Carbon::now()->format('Y-m-d H:i:s'), $cartItem->expired_at);
                    $cartItem->reserved_time = $reservedTime;
                    if($reservedTime == CART_ITEM_TIME_OUT)
                    {
                        if(!$this->expireCartItemForCustomer($cartItem))
                        {
                            return false;
                        }
                    }
                    if(!$cartItem->save())
                    {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function getUserCartDetail($inputs, $isAdmin = false)
    {
        $userId = $inputs['user_id'];
        $query = $this->cart->newQuery()->whereUserId($userId)->select([
            'id', 'total_before_discount', 'description', 'product_id', 'image', 'mailbox', 'price', 'quantity', 'slug', 'stock', 'title', 'cart_at', 'reserved_time', 'expired_at'
        ]);
        if (!empty($inputs['search'])) {
            $query->whereFuzzy(function ($q) use ($inputs) {
                searchTableFuzzy($q, $inputs['search'], ['title', 'description', 'price', 'quantity']);
            });
        }
        if($isAdmin)
        {
            return $query->get();
        }
        $userQuery = $this->cart->newQuery()->whereUserId($userId)->select([
            'user_id', 'email', 'first_name', 'last_name', 'us_express_number', 'created_at', 'expired_at'
        ]);
        $user = $userQuery->orderBy('expired_at', 'DESC')->first();
        $items = $query->get();
        $data = ['user' => $user, 'items' => $items];
        return $data;
    }

    private function getUserCartDetailForAdmin($userId)
    {
        $query = $this->cart->newQuery()->whereUserId($userId)->select([
            'id', 'description', 'product_id', 'image', 'mailbox', 'price', 'quantity', 'slug', 'stock', 'title', 'cart_at', 'reserved_time', 'expired_at'
        ]);
        return $query->get();
    }

    private function updateAllCartItems($userId)
    {
        if($this->cart->newQuery()->whereUserId($userId)->count() > 0)
        {
            $carts = $this->cart->newQuery()->whereUserId($userId)->get();
            foreach($carts as $item)
            {
                $item->cart_at = Carbon::now();
                if(!$item->save())
                {
                    return false;
                }
            }
        }

        return true;
    }

    private function isStockUpdated($mailBoxId, $quantity, $cartItem)
    {
        $productMailBox = $this->productMailbox->newQuery()->whereId($mailBoxId)->first();
        if(!$productMailBox)
        {
            return false;
        }
        if($quantity > $productMailBox->stock)
        {
            return false;
        }
        $productMailBox->stock = $productMailBox->stock - $quantity;
        if(!$productMailBox->save())
        {
            return false;
        }
        // cart mailbox column updated
        if(!$this->isCartMailboxUpdated($cartItem, $productMailBox->stock))
        {
            return false;
        }

        return true;
    }

    private function isStockIncOrDec($mailBoxId, $quantity, $cartItem, $is_quantity_increased, $timeOutMinusFromStock)
    {
        $productMailBox = $this->productMailbox->newQuery()->whereId($mailBoxId)->first();
        if(!$productMailBox)
        {
            return false;
        }
        if(!$timeOutMinusFromStock)
        {
            if($is_quantity_increased)
            {
                $productMailBox->stock = $productMailBox->stock - $quantity;
            }else{
                $productMailBox->stock = $productMailBox->stock + $quantity;
            }
        }else{
            $productMailBox->stock = $productMailBox->stock - $quantity;
        }


        if(!$productMailBox->save())
        {
            return false;
        }
        // cart mailbox column updated
        if(!$this->isCartMailboxUpdated($cartItem, $productMailBox->stock))
        {
            return false;
        }

        return true;
    }



    private function isStockUpdatedWhenTimeUpdated($mailBoxId, $quantity, $cartItem)
    {
        $productMailBox = $this->productMailbox->newQuery()->whereId($mailBoxId)->first();
        if(!$productMailBox)
        {
            return false;
        }
        if($productMailBox->stock <= 0)
        {
            $quantity = 0;
        }
        while($quantity > $productMailBox->stock && $productMailBox->stock > 0)
        {
            $quantity--;
        }

        $productMailBox->stock = $productMailBox->stock - $quantity;
        if(!$productMailBox->save())
        {
            return false;
        }
        // cart mailbox column updated
        if(!$this->isCartMailboxUpdated($cartItem, $productMailBox->stock, $quantity))
        {
            return false;
        }

        return true;
    }

    private function isCartMailboxUpdated($cartItem, $updatedStockValue, $quantity = null)
    {
        $data = [];
        foreach($cartItem->mailbox as $key => $value)
        {
            if($key == 'stock')
            {
                $data[$key] = (integer) $updatedStockValue;
            }else{
                $data[$key] = $value;
            }
        }
        $cartItem->mailbox = $data;
        if($quantity)
        {
            $cartItem->quantity = $quantity;
        }
        if(!$cartItem->save())
        {
            return false;
        }
        return true;
    }

    private function isQuantityAvailable($inputs)
    {
        $productMailBox = $this->productMailbox->newQuery()->whereId($inputs['item']['mailbox']['id'])->first();
        // DD($productMailBox);
        if(!$productMailBox)
        {
            return false;
        }
        if($inputs['item']['quantity'] > $productMailBox->stock)
        {
            return false;
        }

        return true;
    }

    private function isStockPreUpdated($cartItem)
    {
        if($cartItem->reserved_time != CART_ITEM_TIME_OUT)
        {
            $productMailBox = $this->productMailbox->newQuery()->whereId($cartItem->mailbox['id'])->first();
            if(!$productMailBox)
            {
                return false;
            }
            $productMailBox->stock = $productMailBox->stock + $cartItem->quantity;
            if(!$productMailBox->save())
            {
                return false;
            }
        }

        return true;
    }

    private function isStockPreUpdatedWhenUserCartDelete($userId)
    {
        $cartList = $this->cart->newQuery()->whereUserId($userId)->where('reserved_time', '!=', CART_ITEM_TIME_OUT)->get();
        foreach($cartList as $key => $cartItem)
        {
            if(!$this->isStockPreUpdated($cartItem))
            {
                return false;
            }
        }
        return true;
    }

    private function isQuantityAvailableForQuantityUpdate($cartItem, $inputs)
    {
        $productMailBox = $this->productMailbox->newQuery()->whereId($cartItem->mailbox['id'])->first();
        if(!$productMailBox)
        {
            return false;
        }
        if($inputs['quantity'] > $productMailBox->stock)
        {
            return false;
        }
        return true;
    }

    private function updateIsLastUpdatedColumn($cartItem)
    {
        $this->cart->newQuery()->whereUserId($cartItem->user_id)->update(['is_last_updated' => false]);
        $cartItem->is_last_updated = true;
        if(!$cartItem->save())
        {
            return false;
        }
        return true;
    }

    private function updateIsLastUpdatedColumnForDelete($cartItem)
    {
        if($cartItem->is_last_updated)
        {
            $this->cart->newQuery()->whereUserId($cartItem->user_id)->update(['is_last_updated' => false]);
            $newCartItem = $this->cart->newQuery()->where('id', '!=', $cartItem->id)->whereUserId($cartItem->user_id)->orderBy('id', 'DESC')->first();
            if(!$newCartItem)
            {
                return false;
            }
            $newCartItem->is_last_updated = true;
            if(!$newCartItem->save())
            {
                return false;
            }
        }

        return true;
    }

    private function getExpiredAt($cart_at)
    {
        $keys = ['item_title', 'item_time'];
        $setting = Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();
        if(count($setting) != 2)
        {
            return false;
        }
        if(count($setting) == 2)
        {
            $cartItemExpiredTime = $setting['item_title'].' '.$setting['item_time'];
            $cartItemExpiredTimeSeconds = Carbon::parse($cartItemExpiredTime)->diffInSeconds() + 1;

            $cart_at_seconds = strtotime($cart_at);
            $expired_at_seconds = $cart_at_seconds + $cartItemExpiredTimeSeconds;
            $expired_at = Carbon::parse($expired_at_seconds)->format('Y-m-d H:i:s');
            return [true, $expired_at];
        }
        return [false, NULL];

    }

    private function calculateUserCartExpiredAt($cart_at)
    {
        $keys = ['cart_title', 'cart_time'];
        $setting = Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();
        if(count($setting) != 2)
        {
            return false;
        }
        if(count($setting) == 2)
        {
            $userCartExpiredAtTime = $setting['cart_title'].' '.$setting['cart_time'];
            $userCartExpiredAtTimeSeconds = Carbon::parse($userCartExpiredAtTime)->diffInSeconds() + 1;

            $cart_at_seconds = strtotime($cart_at);
            $cart_expired_at_seconds = $cart_at_seconds + $userCartExpiredAtTimeSeconds;
            $cart_expired_at = Carbon::parse($cart_expired_at_seconds)->format('Y-m-d H:i:s');
            return [true, $cart_expired_at];
        }
        return [false, NULL];

    }

    private function isCartAtExpiredAtReservedTimeUpdated($userId, $updatedCartItemId = null)
    {
        if(!$this->isTimeOutCartItemsStockMaintained($userId, $updatedCartItemId))
        {
            return false;
        }

        $cart_at = Carbon::now()->format('Y-m-d H:i:s');
        $result = $this->getExpiredAt($cart_at);
        if(!$result[0])
        {
            return false;
        }
        $expired_at = $result[1];
        $reserved_time = getReservedTime($cart_at, $expired_at);

        $this->cart->newQuery()->whereUserId($userId)->update([
            'cart_at' => $cart_at,
            'expired_at' => $expired_at,
            'reserved_time' => $reserved_time
        ]);

        return true;
    }

    private function isQuantityDeductedFromStockAfterTimeUpdate($userId, $cartItem)
    {
        $cartList = $this->cart->newQuery()->whereUserId($userId)
            ->where('id', '!=', $cartItem->id)
            ->where('reserved_time', '!=', CART_ITEM_TIME_OUT)->get();
        if(count($cartList) > 0)
        {
            foreach($cartList as $key => $cartItem)
            {
                if(!$this->isStockUpdated($cartItem->mailbox['id'], $cartItem->quantity, $cartItem))
                {
                    return false;
                }
            }
        }
        return true;
    }

    public function isUserCartExpiredAtUpdated($userId)
    {
        $count = $this->cart->newQuery()->whereUserId($userId)->count();
        if($count > 0)
        {
            $cartItem = $this->cart->newQuery()->whereUserId($userId)->orderBy('cart_at', 'DESC')->first();
            $cart_at = Carbon::parse($cartItem->cart_at)->format('Y-m-d H:i:s');
            $result = $this->calculateUserCartExpiredAt($cart_at);
            if(!$result[0])
            {
                return false;
            }
            $cart_expired_at = $result[1];
            $this->cart->newQuery()->whereUserId($userId)->update([
                'cart_expired_at' => $cart_expired_at
            ]);
        }

        return true;
    }

    private function isTimeOutCartItemsStockMaintained($userId, $updatedCartItemId = null)
    {
        $cartList = $this->cart->newQuery()->whereUserId($userId)->whereReservedTime(CART_ITEM_TIME_OUT);
        if($updatedCartItemId)
        {
            $cartList->where('id', '!=', $updatedCartItemId);
        }
        $cartList = $cartList->get();
        if(count($cartList) > 0)
        {
            foreach($cartList as $key => $cartItem)
            {
                if(!$this->isStockUpdatedWhenTimeUpdated($cartItem->mailbox['id'], $cartItem->quantity, $cartItem))
                {
                    return false;
                }
            }
        }
        return true;
    }

    private function testCartMailBox()
    {
        $cart = $this->cart->newQuery()->whereUserId(2)->orderBy('id', 'DESC')->first();
        $this->isStockPreUpdated($cart);
        dd($cart->id, $cart->mailbox['id']);
    }

    private function getUserCartExpiredAt($userId)
    {
        $cartItem = $this->cart->newQuery()->whereUserId($userId)->orderBy('cart_expired_at', 'DESC')->first();
        if($cartItem)
        {
            return $cartItem->cart_expired_at;
        }
        return NULL;
    }

    private function getCartItemExpiredAt($userId)
    {
        $cartItem = $this->cart->newQuery()->whereUserId($userId)->orderBy('expired_at', 'DESC')->first();
        if($cartItem)
        {
            return $cartItem->expired_at;
        }
        return NULL;
    }

    private function getLastUpdatedAtCartItem($userId)
    {
        $cartItem = $this->cart->newQuery()->whereUserId($userId)->orderBy('cart_at', 'DESC')->first();
        if($cartItem)
        {
            return $cartItem->cart_at;
        }
        return NULL;
    }


    private function getUserCartCreatedAt($userId)
    {
        $cartItem = $this->cart->newQuery()->whereUserId($userId)->orderBy('created_at', 'ASC')->first();
        if($cartItem)
        {
            return $cartItem->created_at;
        }
        return NULL;
    }

    private function isStockAvailableInProductMailBox($cartItem, $quantity, $is_quantity_increased, $timeOutMinusFromStock)
    {
        if($is_quantity_increased || $timeOutMinusFromStock)
        {
            $productMailBox = $this->productMailbox->newQuery()->whereId($cartItem->mailbox['id'])->first();
            if(!$productMailBox)
            {
                return false;
            }
            if($quantity > $productMailBox->stock)
            {
                return false;
            }
        }

        return true;
    }

    private function isValidatedPriceSaved($cartItem)
    {
        $productMailBox = $this->productMailbox->newQuery()->whereId($cartItem->mailbox['id'])->first();
        if($productMailBox && $productMailBox->cost_after_discount)
        {
            $cartItem->price = $productMailBox->cost_after_discount;
            if(!$cartItem->save())
            {
                return false;
            }
        }
        return true;
    }

}
