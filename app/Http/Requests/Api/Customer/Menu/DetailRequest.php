<?php

namespace App\Http\Requests\Api\Customer\Menu;

use App\Http\Requests\Api\BaseRequest;

class DetailRequest extends BaseRequest
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
            'id' => 'required_without:slug|integer|exists:menus,id',
            'slug' => 'required_without:id|string|exists:menus,slug',
        ];
    }
}
