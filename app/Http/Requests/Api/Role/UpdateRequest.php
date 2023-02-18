<?php

namespace App\Http\Requests\Api\Role;

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
            'roles' => 'required|array',
            'roles.*.id' => 'required|integer|exists:roles,id',
            'roles.*.permissions' => 'required|array',
            'roles.*.permissions.*.id' => 'required|integer|exists:permissions,id',
            'roles.*.permissions.*.read' => 'required|boolean',
            'roles.*.permissions.*.write' => 'required|boolean',
        ];
    }
}
