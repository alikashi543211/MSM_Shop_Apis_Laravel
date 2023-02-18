<?php

namespace App\Http\Requests\Api\Product;

use App\Http\Requests\Api\BaseRequest;

class UpdateProductRequest extends BaseRequest
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
            'id' => 'required|exists:products,id',
            'product_mail_boxes' => 'required|array',
            'product_mail_boxes.*.id' => 'required|exists:product_mail_boxes,id',
            'product_mail_boxes.*.product_id' => 'required|exists:products,id',
            'product_mail_boxes.*.landed_cost' => 'required|numeric',
            'product_mail_boxes.*.discount' => 'required|numeric',
            'product_merchants.*.id' => 'required|exists:product_merchants,id',
            'product_merchants.*.product_id' => 'required|exists:products,id',
            'product_merchants.*.estimated_landed_cost' => 'required|numeric',
            'product_merchants.*.link' => 'required',
        ];
    }
}
