<?php

namespace App\Http\Requests\Api\ProductCatalogue;

use App\Http\Requests\Api\BaseRequest;

class StoreProductTagRequest extends BaseRequest
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
            'category_tags' => 'required|array',
            'category_tags.*.tag_id' => 'required|integer|exists:tags,id',
            'category_tags.*.category_id' => 'required|integer|exists:categories,id',
        ];
    }
}
