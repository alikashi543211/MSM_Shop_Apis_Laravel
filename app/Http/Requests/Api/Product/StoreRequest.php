<?php

namespace App\Http\Requests\Api\Product;

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
            'title' => 'required|string',
            'description' => 'required',
            'published_at' => 'nullable|date|date_format:Y-m-d',
            'expired_at' => 'nullable|date|date_format:Y-m-d',
            'in_stock' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'is_buy_now' => 'nullable|boolean',
            'show_buying_options' => 'nullable|boolean',
            'product_attributes' => 'nullable|array',
            'product_attributes.*.attribute' => 'nullable|array',
            'product_attributes.*.attribute.id' => 'nullable|exists:product_attributes,id',
            'product_attributes.*.attribute.key' => 'nullable|string|max:200',
            'product_attributes.*.attribute.value' => 'nullable|string|max:200',
        ];
    }
}
