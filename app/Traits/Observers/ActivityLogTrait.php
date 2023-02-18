<?php

namespace App\Traits\Observers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Description of ActivityLog
 *
 * @author Muhammad Abid
 */
trait ActivityLogTrait
{

    /**
     * Description: The following method sotre all database action into activity_logs table
     * @author Muhammad Abid - I2L
     * @param $model
     * @param $action
     * @return array
     */
    public function activityLog($model, $action)
    {
        if($model->id){

            $data['data']       = json_encode(DB::table($model->getTable())->where('id', $model->id)->first());
            $data['model_id']   = $model->id;
            $data['model']      = class_basename($model);
            $data['table']      = $model->getTable();
            $data['action']     = $action;
            $data['user_id']    = Auth::check() ? Auth::id() : null;

            $activityLog = new ActivityLog();
            $activityLog->fill($data);
            $activityLog->save();
        }
    }

}
