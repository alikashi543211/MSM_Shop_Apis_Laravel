<?php

namespace App\Http\Requests\Api\Customer\Setting;

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
            'keys' => 'nullable|array',
            'keys.*' => 'exists:settings,key',
        ];
    }
}
