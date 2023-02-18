<?php

namespace App\Http\Requests\Api\Customer\Cart;

use App\Http\Requests\Api\BaseRequest;
use Illuminate\Support\Facades\Log;

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
            'user_id' => 'required|integer',
            'email' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'us_express_number' => 'required|integer',
            'item' => 'required|array',
            'item.description' => 'required|string',
            'item.product_id' => 'required|integer|exists:products,id',
            'item.image' => 'required',
            'item.mailbox' => 'required|array',
            'item.mailbox.id' => 'required',
            'item.mailbox.stock' => 'required',
            'item.price' => 'required',
            'item.quantity' => 'required|integer',
            'item.slug' => 'required|string',
            'item.stock' => 'required|integer',
            'item.title' => 'required|string',
        ];
    }
}
