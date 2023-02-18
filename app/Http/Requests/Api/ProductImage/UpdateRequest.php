<?php

namespace App\Http\Requests\Api\ProductImage;

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
            'product_id' => 'required|integer|exists:products,id',
            'id' => 'required|integer|exists:product_images,id',
            'description' => 'nullable|string',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
            'image_style' => 'required|in:' . FIT_IMAGE_STYLE . ',' . FILL_IMAGE_STYLE,
        ];
    }
}
