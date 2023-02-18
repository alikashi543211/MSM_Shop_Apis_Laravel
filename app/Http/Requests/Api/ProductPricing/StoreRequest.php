<?php

namespace App\Http\Requests\Api\ProductPricing;

use App\Http\Requests\Api\BaseRequest;

class StoreRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'pricing' => 'required|array|min:1',
            'pricing.mail_boxes' => 'nullable|array',
            'pricing.mail_boxes.*.id' => 'nullable|exists:product_mail_boxes,id',
            'pricing.mail_boxes.*.sku' => 'required|string|max:200',
            'pricing.mail_boxes.*.landed_cost' => 'required|numeric ',
            'pricing.mail_boxes.*.location' => 'required|string|max:200',
            'pricing.mail_boxes.*.stock' => 'required|integer',
            'pricing.mail_boxes.*.discount' => 'required|numeric',
            'pricing.mail_boxes.*.discount_type' => 'required|in:'.PERCENTAGE_DISCOUNT_TYPE.','.FLAT_DISCOUNT_TYPE,
            'pricing.merchants' => 'nullable|array',
            'pricing.merchants.*.id' => 'nullable|exists:product_merchants,id',
            'pricing.merchants.*.merchant_id' => 'required|integer|exists:merchants,id',
            'pricing.merchants.*.link' => 'required|string',
            'pricing.merchants.*.retail_cost' => 'required|numeric',
            'pricing.merchants.*.duty' => 'required|numeric',
            'pricing.merchants.*.wharfage' => 'required|numeric',
            'pricing.merchants.*.shipping' => 'required|numeric',
            'pricing.merchants.*.fuel_adjustment' => 'required|numeric',
            'pricing.merchants.*.insurance' => 'required|numeric',
            'pricing.merchants.*.estimated_landed_cost' => 'required|numeric',
        ];
    }
}
