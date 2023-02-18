<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\Setting\ListingRequest;
use App\Models\Setting;
use App\Traits\Api\SettingTrait;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use SettingTrait;
    private $setting;

    public function __construct()
    {
        $this->setting = new Setting();
    }

    public function listing(ListingRequest $request)
    {
        $inputs = $request->all();
        $query = $this->setting->newQuery();
        if(!empty($inputs['keys']))
        {
            $query->whereIn('key', $inputs['keys']);
        }
        if(!empty($inputs['search']))
        {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['key', 'value']);
            });
        }
        $settings = $query->get();
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $settings);
    }
}
