<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\BaseRequest;
use App\Rules\PhoneNumberRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            'id' => 'required|exists:users,id',
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email:dns,rfc|unique:users,email,'.request('id').',id',
            'phone_no' => ['required','numeric',new PhoneNumberRule()],
            'password' => 'sometimes|required|string|min:8',
            'role_id' => 'required|exists:roles,id|in:' . ROLE_ADMIN . ',' . ROLE_EDITOR . ',' . ROLE_MANAGER,
        ];
    }
}
