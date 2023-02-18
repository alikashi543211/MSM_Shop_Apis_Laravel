<?php

namespace App\Http\Requests\Api\Pdf;

use App\Http\Requests\Api\BaseRequest;

class DownloadPdfRequest extends BaseRequest
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
            'created_at' => 'required',
            'name' => 'nullable',
            'us_express_number' => 'nullable',
            'business_reference_id' => 'required',
            'delivery' => 'required|boolean',
            'house_name' => 'nullable',
            'house_number' => 'nullable',
            'street' => 'nullable',
            'parish' => 'nullable',
            'postal_code' => 'nullable',
            'card' => 'nullable',
            'items' => 'required|array',
            'items.*.description' => 'required',
            'items.*.landed_cost' => 'required',
            'items.*.quantity' => 'required|integer',
            'items.*.price' => 'required|numeric',
            'total_price' => 'required|numeric',
            'total_discount' => 'required|numeric',
            'delivery_fee' => 'nullable|numeric',
        ];
    }
}
