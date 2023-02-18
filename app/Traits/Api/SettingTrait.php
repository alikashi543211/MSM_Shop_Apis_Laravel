<?php
namespace App\Traits\Api;

use Carbon\Carbon;

trait SettingTrait
{
    private function getCartItemExpiredAt($cart_at)
    {
        $keys = ['item_title', 'item_time'];
        $setting = $this->setting->newQuery()->whereIn('key', $keys)->pluck('value', 'key')->toArray();
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

    private function isCartItemExpiredAtAndReservedTimeUpdated()
    {
        $carts = $this->cart->newQuery()->get();
        foreach($carts as $cartItem)
        {
            $cart_at = $cartItem->cart_at;
            $result = $this->getCartItemExpiredAt($cart_at);
            if(!$result[0])
            {
                return false;
            }
            $expired_at = $result[1];
            $reserved_time = getReservedTime($cart_at, $expired_at);

            $this->cart->newQuery()->update([
                'cart_at' => $cart_at,
                'expired_at' => $expired_at,
                'reserved_time' => $reserved_time
            ]);
        }

        return true;

    }

    private function isUserCartExpiredAtUpdatedSetting()
    {
        $count = $this->cart->newQuery()->count();

        $userIds = $this->cart->newQuery()->select('user_id')->distinct()->pluck('user_id')->toArray();
        if(count($userIds) > 0)
        {
            foreach($userIds as $key => $userId)
            {
                if(!$this->isUserCartExpiredAtUpdated($userId))
                {
                    return false;
                }
            }
        }

        return true;
    }

    private function isUserCartExpiredAtUpdated($userId)
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

    private function calculateUserCartExpiredAt($cart_at)
    {
        $keys = ['cart_title', 'cart_time'];
        $setting = $this->setting->newQuery()->whereIn('key', $keys)->pluck('value', 'key')->toArray();
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


}
