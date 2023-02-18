<?php

namespace App\Http\Requests\Api\Menu;

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
            'id' => 'required|integer|exists:menus,id',
            'title' => 'required|string|unique:menus,title,'.request('id').',id',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
            'background_color' => 'sometimes|required|string|max:100',
            'text_color' => 'required|in:' . DARK_TEXT_COLOR . ',' . LIGHT_TEXT_COLOR,
            'image_style' => 'required|in:' . FIT_IMAGE_STYLE . ',' . FILL_IMAGE_STYLE,
        ];
    }
}
