<?php

namespace App\Http\Requests\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;


class BaseRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $json = [
            'success' => false,
            'message' => $validator->errors()->all(),
        ];
        $response = new JsonResponse($json, 400);
        throw (new ValidationException($validator, $response))->status(400);
    }
}
