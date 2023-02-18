<?php

namespace App\Models;

use Hamcrest\Arrays\IsArray;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'product_id', 'image', 'mailbox', 'price', 'quantity', 'slug', 'stock', 'email', 'first_name', 'last_name', 'user_id', 'us_express_number'
    ];

    protected $casts = [
        "mailbox" => "json",
        "image" => "array",
    ];

    public function getTotalBeforeDiscountAttribute()
    {
        if(isset($this->mailbox['landed_cost']) && $this->quantity)
        {
            return twoDecimal($this->quantity * $this->mailbox['landed_cost']);
        }
        return null;
    }

    // public function getMailboxAttribute($value)
    // {
    //     if(isset($value))
    //     {
    //         $mailbox = (array) json_decode($value);
    //         if(is_array($mailbox) && count($mailbox) > 0)
    //         {
    //             if(isset($mailbox['discount_amount']))
    //             {
    //                 $mailbox['discount_amount'] = twoDecimal($mailbox['discount_amount']);
    //             }
    //             if(isset($mailbox['cost_after_discount']))
    //             {
    //                 $mailbox['cost_after_discount'] = twoDecimal($mailbox['cost_after_discount']);
    //             }
    //             if(isset($mailbox['discount']))
    //             {
    //                 $mailbox['discount'] = twoDecimal($mailbox['discount']);
    //             }
    //             $value = json_encode($mailbox);
    //         }
    //     }

    //     return $value;
    // }


}
