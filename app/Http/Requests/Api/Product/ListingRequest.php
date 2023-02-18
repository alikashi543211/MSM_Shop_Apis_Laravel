<?php

namespace App\Http\Requests\Api\Product;

use App\Http\Requests\Api\BaseRequest;

class ListingRequest extends BaseRequest
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
            'menu_id' => 'nullable|integer|exists:menus,id',
            'tag_title' => 'nullable|array',
            'tag_title.*' => 'string',
            'top_filter' => 'nullable|string|in:'.ALL_FILTER.','.DISABLED_FILTER.','.ACTIVE_FILTER.','.NO_MERCHANTS_FILTER.','.NOT_FOR_SALE_FILTER,
        ];
    }
}
