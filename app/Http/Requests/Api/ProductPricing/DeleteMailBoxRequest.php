<?php

namespace App\Http\Requests\Api\ProductPricing;

use App\Http\Requests\Api\BaseRequest;

class DeleteMailBoxRequest extends BaseRequest
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
            'id' => 'required|integer|exists:product_mail_boxes,id',
        ];
    }
}
