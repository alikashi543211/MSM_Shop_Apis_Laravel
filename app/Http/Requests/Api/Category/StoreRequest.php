<?php

namespace App\Http\Requests\Api\Category;

use App\Http\Requests\Api\BaseRequest;
use App\Rules\CategoryTitleUniqueRule;

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
            'menu_id' => 'required|integer|exists:menus,id',
            'title' => ['required', 'string', new CategoryTitleUniqueRule(request('menu_id'))],
        ];
    }
}
