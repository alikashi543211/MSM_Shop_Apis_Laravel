<?php

namespace App\Http\Requests\Api\ProductImage;

use App\Http\Requests\Api\BaseRequest;
use App\Models\ProductImage;

class UpdateSortingRequest extends BaseRequest
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
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:product_images,id',
        ];
    }
}
