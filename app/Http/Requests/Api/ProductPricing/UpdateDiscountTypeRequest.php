<?php

namespace App\Http\Requests\Api\ProductPricing;

use App\Http\Requests\Api\BaseRequest;

class UpdateDiscountTypeRequest extends BaseRequest
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
            'id' => 'required|exists:product_mail_boxes,id',
            'discount_type' => 'required|in:'.PERCENTAGE_DISCOUNT_TYPE.','.FLAT_DISCOUNT_TYPE,
        ];
    }
}
