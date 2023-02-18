<?php

namespace App\Http\Requests\Api\Menu;

use App\Http\Requests\Api\BaseRequest;

class ImageUpdateRequest extends BaseRequest
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
            'id' => 'required|integer|exists:menus,id',
            'text_color' => 'sometimes|required|in:' . DARK_TEXT_COLOR . ',' . LIGHT_TEXT_COLOR,
            'image_style' => 'sometimes|required|in:' . FIT_IMAGE_STYLE . ',' . FILL_IMAGE_STYLE,
        ];
    }
}
