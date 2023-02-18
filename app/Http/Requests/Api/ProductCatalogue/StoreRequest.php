<?php

namespace App\Http\Requests\Api\ProductCatalogue;

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
            'menus' => 'nullable|array',
            'menus.*.id' => 'nullable|integer|exists:product_menus,id',
            'menus.*.menu_id' => 'required|integer|exists:menus,id',
            'tags' => 'nullable|array',
            'tags.*.category_tags' => 'required|array',
            'tags.*.category_tags.id' => 'nullable|integer|exists:product_tags,id',
            'tags.*.category_tags.tag_id' => 'required|integer|exists:tags,id',
            'tags.*.category_tags.category_id' => 'required|integer|exists:categories,id',
        ];
    }
}
