<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Setting\ListingRequest;
use App\Http\Requests\Api\Setting\StoreRequest;
use App\Models\Cart;
use App\Models\Setting;
use App\Traits\Api\CartTrait;
use App\Traits\Api\SettingTrait;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    use SettingTrait;
    private $setting, $cart;

    public function __construct()
    {
        $this->setting = new Setting();
        $this->cart = new Cart();
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

    public function store(StoreRequest $request)
    {
        try
        {
            DB::beginTransaction();
            $inputs = $request->all();
            foreach($inputs['setting'] as $key => $data)
            {
                $setting = $this->setting->newQuery()->where('key', $data['key'])->first() ?? $this->setting->newInstance();
                if ($request->hasFile($data['key'])) {
                    $image_path =  uploadFile($data['value'], 'uploads/setting', $data['key']);
                    $setting->key = $data['key'];
                    $setting->value = $image_path;
                } else{
                    $setting->fill($data);
                }
                if(!$setting->save())
                {
                    DB::rollback();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }
            }
            if(!$this->isCartItemExpiredAtAndReservedTimeUpdated())
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$this->isUserCartExpiredAtUpdatedSetting())
            {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successResponse(GENERAL_SUCCESS_MESSAGE);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

}
