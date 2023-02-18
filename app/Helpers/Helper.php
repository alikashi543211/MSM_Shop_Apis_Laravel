<?php

use App\Models\JobNote;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

function errorResponse($message, $code)
{
    return response()->json([
        'success' => false,
        'message' => [$message]
    ], $code);
}

function successResponse($message)
{
    return response()->json([
        'success' => true,
        'message' => $message
    ], SUCCESS_200);
}

function successDataResponse($message, $data)
{
    return response()->json([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], SUCCESS_200);
}

function uploadFile($file, $path, $name)
{
    $path = $path . '/' . date('Y') . '/' . date('m') . '/';
    $filename = time().'-'.Str::random(4).'-'.$name.'.'.$file->getClientOriginalExtension();
    $file->move($path, $filename);
    return $path.'/'.$filename;
}

function sendMail($data)
{
    Mail::send($data['view'], $data['data'], function ($send) use($data)
    {
        $send->to($data['to'])->subject($data['subject']);
    });
    return true;
}

function getUniqueSlug($value)
{
    $code = $code = Str::random(6);
    $slug = preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($value))).'-'.$code;
    return $slug;
}

function searchTable($query, $keyword, $filters, $with = null)
{
    if ($with) {
        $query->orWhereHas($with, function ($q) use ($filters, $keyword) {
            foreach ($filters as $key => $column) {
                if ($key == 0) {
                    $q->where($column, 'LIKE', '%' . $keyword . '%');
                } else {
                    $q->orWhere($column, 'LIKE', '%' . $keyword . '%');
                }
            }
        });
    } else {
        foreach ($filters as $key => $column) {
            $query->orWhere($column, 'LIKE', '%' . $keyword . '%');
        }
    }

    return $query;
}

function searchTableWithoutExplode($query, $keyword, $filters, $with = null)
{
    if ($with) {
        $query->orWhereHas($with, function ($q) use ($filters, $keyword) {
            foreach ($filters as $key => $column) {
                if ($key == 0) {
                    $q->where($column, 'LIKE', '%' . $keyword . '%');
                } else {
                    $q->orWhere($column, 'LIKE', '%' . $keyword . '%');
                }
            }
        });
    } else {
        foreach ($filters as $key => $column) {
            $query->orWhere($column, 'LIKE', '%' . $keyword . '%');
        }
    }

    return $query;
}
function searchTableFuzzy($query, $keyword, $filters, $with = null, $explod = false)
{
    if ($with) {
        $query->orWhereHas($with, function ($q) use ($filters, $keyword, $explod) {
            foreach ($filters as $key => $column) {
                if($explod){
                    foreach(explode(' ', $keyword) AS $index => $word){
                        if($index == 0){
                            $q->whereFuzzy($column, $word);
                        }else{
                            $q->orWhereFuzzy($column, $word);
                        }
                    }
                }else{
                    if ($key == 0) {
                        $q->whereFuzzy($column, $keyword);
                    } else {
                        $q->orWhereFuzzy($column, $keyword);
                    }
                }
            }
        });
    } else {
        foreach ($filters as $key => $column) {
            if($explod){
                foreach(explode(' ', $keyword) AS $index => $word){
                    if($index == 0){
                        $query->whereFuzzy($column, $word);
                    }else{
                        $query->orWhereFuzzy($column, $word);
                    }
                }
            }else{
                $query->orWhereFuzzy($column, $keyword);
            }
        }
    }
    // dd($query);
    return $query;
}

function generateVerificationCode($table = 'users', $col = 'verification_code')
{
    do {
        $verficationCode = rand(111111, 999999);
    } while (DB::table($table)->where($col, $verficationCode)->exists());
    return $verficationCode;
}

function generateVerificationToken($table = 'users', $col = 'verification_token')
{
    do {
        $verficationCode = Str::random(100);
    } while (DB::table($table)->where($col, $verficationCode)->exists());
    return $verficationCode;
}


function twoDecimal($number)
{
    return number_format((float) $number, 2, '.', '');
}


function getReservedTime($cart_at, $expired_at)
{
    // Log::info('Now System Time : '. Carbon::now()->format('Y-m-d H:i:s'));
    $cart_at = Carbon::parse($cart_at);
    $expired_at = Carbon::parse($expired_at);
    if(!$expired_at->gt($cart_at))
    {
        return 'Timeout';
    }
    $dayCount = $cart_at->longAbsoluteDiffForHumans($expired_at);
    $dayCount = $cart_at->diffInDays($expired_at);
    $hourCount = $cart_at->diffInHours($expired_at);
    $minuteCount = $cart_at->diffInMinutes($expired_at);
    $secondCount = $cart_at->diffInSeconds($expired_at);

    $dayString = getReservedTimeString($dayCount, "days", 'day');
    $hourString = getReservedTimeString($hourCount, "hours", 'hour');
    $minuteString = getReservedTimeString($minuteCount, "minutes", 'minute');
    $secondString = getReservedTimeString($secondCount, "seconds", 'second');
    if($dayString)
    {
        $reservedTime = $dayString;
    }elseif($hourString)
    {
        $reservedTime = $hourString;
    }elseif($minuteString)
    {
        $reservedTime = $minuteString;
    }elseif($secondString)
    {
        $reservedTime = $secondString;
    }else{
        $reservedTime = CART_ITEM_TIME_OUT;
    }

    return $reservedTime;
}

function getReservedTimeString($count, $plural, $singular)
{
    if($count > 0)
    {
        $timeString = $plural;
        if($count == 1)
        {
            $timeString = $singular;
        }
        return $count.' '.$timeString;
    }
    return NULL;
}
