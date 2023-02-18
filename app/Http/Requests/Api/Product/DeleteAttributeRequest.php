<?php

namespace App\Http\Requests\Api\Product;

use App\Http\Requests\Api\BaseRequest;

class DeleteAttributeRequest extends BaseRequest
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
            'id' => 'required|exists:product_attributes,id',
        ];
    }
}
