<?php
namespace App\Traits\Api;


trait ProductPricingTrait
{
    private function saveProductMailBoxes($inputs)
    {
        if(!empty($inputs['pricing']['mail_boxes']))
        {
            foreach($inputs['pricing']['mail_boxes'] as $key => $value)
            {
                if(isset($value['id']))
                {
                    $productMailbox = $this->productMailbox->newQuery()->whereId($value['id'])->first();
                }else{
                    $productMailbox = $this->productMailbox->newInstance();
                }

                $productMailbox->fill($value);
                $productMailbox->product_id = $inputs['product_id'];
                if(!$productMailbox->save())
                {
                    return false;
                }
                if(!$this->isSavedCostAfterDiscount($productMailbox))
                {
                    return false;
                }
            }
        }
        return true;
    }

    private function getProductMailBoxesAndMerchants($productId)
    {
        $data = [];
        $data['product_mail_boxes'] = $this->productMailbox->newQuery()->whereProductId($productId)
            ->get();
        $data['product_merchants'] = $this->productMerchant->newQuery()->whereProductId($productId)
            ->get();
        return $data;
    }

    private function isSavedCostAfterDiscount($productMailbox)
    {
        if($productMailbox->discount_type == PERCENTAGE_DISCOUNT_TYPE)
        {
            $discount_amount = twoDecimal(($productMailbox->landed_cost * $productMailbox->discount ) / 100);
            $productMailbox->cost_after_discount = twoDecimal($productMailbox->landed_cost - $discount_amount);
            $productMailbox->discount_amount = $discount_amount;
            if(!$productMailbox->save())
            {
                return false;
            }
        }elseif($productMailbox->discount_type == FLAT_DISCOUNT_TYPE)
        {
            $productMailbox->cost_after_discount = twoDecimal($productMailbox->landed_cost - $productMailbox->discount);
            $productMailbox->discount_amount = $productMailbox->discount;
            if(!$productMailbox->save())
            {
                return false;
            }
        }
        return true;

    }

    private function isCustomerCartMaintained($productMailbox)
    {
        $cartItemIds = [];
        $cartList = $this->cart->newQuery()->get();
        foreach($cartList as $key => $cartItem)
        {
            if($cartItem->mailbox['id'] == $productMailbox->id)
            {
                $cartItemIds[] = $cartItem->id;
            }
        }
        $cartItemCount = $this->cart->newQuery()->whereIn('id', $cartItemIds)->count();

        if($cartItemCount > 0)
        {
            $totalCartItemQuantity = $this->getTotalCartItemQuantityOfMailBox($cartItemIds);
            if($productMailbox->stock < $totalCartItemQuantity)
            {
                $cartList = $this->cart->newQuery()->whereIn('id', $cartItemIds)->orderBy('id', 'DESC')->get();
                foreach($cartList as $cartItem)
                {
                    for($i = $cartItem->quantity; $i > 0; $i--)
                    {
                        $cartItem->quantity = $cartItem->quantity - 1;
                        if(!$cartItem->save())
                        {
                            return false;
                        }
                        $totalCartItemQuantity = $this->getTotalCartItemQuantityOfMailBox($cartItemIds);
                        if($productMailbox->stock >= $totalCartItemQuantity)
                        {
                            return true;
                        }
                    }
                }
            }
        }

        return true;
    }

    private function getTotalCartItemQuantityOfMailBox($cartItemIds)
    {
        return $this->cart->newQuery()->whereIn('id', $cartItemIds)->sum('quantity');
    }

    private function saveProductMerchants($inputs)
    {


        if(!empty($inputs['pricing']['merchants']))
        {

            foreach($inputs['pricing']['merchants'] as $key => $value)
            {
                if(isset($value['id']))
                {
                    $productMerchant = $this->productMerchant->newQuery()->whereId($value['id'])->first();
                }else{
                    $productMerchant = $this->productMerchant->newInstance();
                }

                $productMerchant->fill($value);
                $productMerchant->import_taxes = twoDecimal($value['duty'] + $value['wharfage']);
                $productMerchant->shipping_charges = twoDecimal($value['shipping'] + $value['fuel_adjustment'] + $value['insurance']);
                $productMerchant->product_id = $inputs['product_id'];
                $productMerchant->sort_number = $this->getNextProductMerchantSortNumber($inputs['product_id']);
                if(!$productMerchant->save())
                {
                    return false;
                }

            }
        }


        return true;
    }

    private function validateSizeOfIds($inputs)
    {
        $count = $this->productMerchant->whereProductId($inputs['product_id'])->count();
        $idsCount = count($inputs['ids']);
        if($count != $idsCount)
        {
            return [false, $count];
        }
        return [true];
    }

    private function getNextProductMerchantSortNumber($productId)
    {
        $sort_number = 1;
        if($this->productMerchant->newQuery()->whereProductId($productId)->count() > 0)
        {
            $sort_number = $this->productMerchant->newQuery()->whereProductId($productId)->orderBy('sort_number', 'desc')->value('sort_number') + 1;
        }
        return $sort_number;
    }

    private function isCartDeletedRelatedToMailbox($productMailBoxId)
    {
        $cartList = $this->cart->newQuery()->get();

        if(count($cartList) > 0)
        {
            foreach($cartList as $cartItem)
            {
                if(isset($cartItem->mailbox['id']))
                {
                    if($cartItem->mailbox['id'] == $productMailBoxId)
                    {
                        if(!$cartItem->delete())
                        {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

}
