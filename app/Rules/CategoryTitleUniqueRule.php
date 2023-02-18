<?php

namespace App\Rules;

use App\Models\Category;
use Illuminate\Contracts\Validation\Rule;

class CategoryTitleUniqueRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $menuId, $updatedId;
    public function __construct($menuId, $updatedId = null)
    {
        $this->menuId = $menuId;
        $this->updatedId = $updatedId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $menuId = $this->menuId;
        $updatedId = $this->updatedId;
        if($updatedId)
        {
            return Category::where('id', '!=', $updatedId)->whereMenuId($menuId)->where('title', $value)->doesntExist();
        }else{
            return Category::whereMenuId($menuId)->where('title', $value)->doesntExist();
        }

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute has already been taken.';
    }
}
