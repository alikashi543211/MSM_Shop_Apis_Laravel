<?php

namespace App\Http\Requests\Api\Customer\Cart;

use App\Http\Requests\Api\BaseRequest;

class UpdateRequest extends BaseRequest
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
            'id' => 'required|integer|exists:carts,id',
            'quantity' => 'required|numeric',
            'is_quantity_increased' => 'required|boolean'
        ];
    }
}
