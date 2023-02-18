<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\Auth\SendMobileOtpRequest;
use App\Http\Requests\Api\Auth\VerifyEmailOtpRequest;
use App\Http\Requests\Api\Auth\VerifyForgotPasswordCodeRequest;
use App\Http\Requests\Api\Auth\VerifyMobileOtpRequest;
use App\Http\Requests\Auth\SendEmailOtpRequest;
use App\Jobs\SendMailJob;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use App\Traits\Api\UserTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    use UserTrait;
    private $user, $permission;

    public function __construct()
    {
        $this->user = new User();
        $this->permission = new Permission();
        $this->rolePermission = new RolePermission();
    }

    public function login(LoginRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if (!Auth::attempt($request->only('email', 'password'))) {
                return errorResponse(__('auth.invalid_credentials'), ERROR_400);
            }
            $user = $this->user->newQuery()->where('email', $request->email)->with(['role'])->first();
            if($inputs['remember_me'])
            {
                $user->is_otp_verified = true;
                $user->save();
                return $this->loginUserWithToken($user);
            }
            $data = ['email' => $user->email, 'phone_no' => $user->phone_no];
            DB::commit();
            return successDataResponse(GENERAL_FETCHED_MESSAGE, $data);

        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    private function loginUserWithToken($user)
    {
        $user->permissions = $this->getFormattedPermissions($user->role);
        $response = [
            'user' => $user,
            'access_token' => $user->createToken('bearer_token')->plainTextToken,
            'token_type' => 'Bearer',
        ];
        return successDataResponse('Logged In', $response);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($user = $this->user->newQuery()->where('email', $inputs['email'])->first()) {
                DB::table('password_resets')->where('email', $inputs['email'])->delete();
                $verificationCode = Str::random(100);
                DB::table('password_resets')->insert([
                    'email' => $user->email,
                    'token' => $verificationCode,
                    'created_at' => Carbon::now()
                ]);
                dispatch(new SendMailJob($user->email, 'Reset Password', ['verificationCode' => $verificationCode], 'forgot-password'));
                DB::commit();
                return successResponse(__('passwords.sent'));
            }
            return successResponse(__('passwords.sent'));
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function verifyForgotPasswordCode(VerifyForgotPasswordCodeRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($code = DB::table('password_resets')->where('token', $inputs['verification_code'])->first()) {
                if (strtotime($code->created_at) < strtotime('-1 hour')) {
                    return errorResponse(__('passwords.tokenExpired'), ERROR_400);
                }
                DB::commit();
                return successDataResponse(__('passwords.codeVerified'), ['email' => $code->email]);
            }
            return errorResponse(__('passwords.token'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($code = DB::table('password_resets')->where('token', $inputs['verification_code'])->where('email', $inputs['email'])->first()) {
                if (strtotime($code->created_at) < strtotime('-1 hour')) {
                    return errorResponse(__('passwords.tokenExpired'), ERROR_400);
                }
                DB::table('password_resets')->where('token', $inputs['verification_code'])->where('email', $inputs['email'])->delete();
                $user = $this->user->newQuery()->where('email', $inputs['email'])->first();
                $user->password = Hash::make($inputs['password']);
                $user->save();
                $user->tokens()->delete();
                $response = [
                    'user' => $user,
                    'access_token' => $user->createToken('bearer_token')->plainTextToken,
                    'token_type' => 'Bearer',
                ];
                DB::commit();
                return successDataResponse(__('passwords.reset'), $response);
            }
            return errorResponse(__('passwords.token'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function logout()
    {
        try {
            DB::beginTransaction();
            Auth::user()->currentAccessToken()->delete();
            // Auth::logout();
            DB::commit();
            return successResponse('Logout');
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function sendEmailOtp(SendEmailOtpRequest $request)
    {
        try {
            $inputs = $request->all();
            $otp = $this->getOtpCode();
            $user = $this->user->newQuery()->whereEmail($inputs['email'])->with('role')->first();
            $user->otp = $otp;
            $user->updated_at = Carbon::now();
            if(!$user->save())
            {
                DB::rollBack();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_500);
            }
            DB::commit();
            dispatch(new SendMailJob($inputs['email'], '2FA Code', ['otp' => $otp], 'email_otp'));
            return successResponse(__('auth.otp_sent'));
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }

    }

    private function getOtpCode()
    {
        return sprintf("%04d", mt_rand(1, 9999));
    }

    public function sendMobileOtp(SendMobileOtpRequest $request)
    {
        try
        {
            $inputs = $request->all();
            $basic  = new \Vonage\Client\Credentials\Basic(VONAGE_KEY, VONAGE_TOKEN);
            $client = new \Vonage\Client($basic);
            $otpCode = $this->getOtpCode();
            $number = $inputs['phone_no'];
            $number = str_replace('-', '', $number);
            $user = $this->user->newQuery()->wherePhoneNo($inputs['phone_no'])->with('role')->first();
            $user->otp = $otpCode;
            $user->updated_at = Carbon::now();
            if(!$user->save())
            {
                DB::rollBack();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_500);
            }
            $body = "Hello {$user->name}! Your Dashboard 2FA code is: {$otpCode}";
            $response = $client->sms()->send(
                new \Vonage\SMS\Message\SMS($number, 'Mlbxs', $body)
            );

            $message = $response->current();

            if ($message->getStatus() == 0) {
                DB::commit();
                return successResponse(__('auth.mobile_otp_sent'));
            } else {
                return errorResponse("The message failed with status: " . $message->getStatus(), ERROR_500);
            }
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }

    }

    public function verifyEmailOtp(VerifyEmailOtpRequest $request)
    {
        try {
            $inputs = $request->all();
            $user = $this->user->newQuery()->whereEmail($inputs['email'])->with('role')->first();
            $updatedAtTime = $user->updated_at;
            $nowTime = Carbon::now();
            $diffInMinutes = $nowTime->diffInMinutes($updatedAtTime);
            if ($diffInMinutes >= 5) {
                return errorResponse(GENERAL_TIME_EXCEED_ERROR, ERROR_500);
            }
            $user->is_otp_verified = true;
            if(!$user->save())
            {
                DB::rollBack();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_500);
            }
            DB::commit();
            return $this->loginUserWithToken($user);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function verifyMobileOtp(VerifyMobileOtpRequest $request)
    {
        try {
            $inputs = $request->all();
            $user = $this->user->newQuery()->wherePhoneNo($inputs['phone_no'])->with('role')->first();
            $updatedAtTime = $user->updated_at;
            $nowTime = Carbon::now();
            $diffInMinutes = $nowTime->diffInMinutes($updatedAtTime);
            if ($diffInMinutes >= 5) {
                return errorResponse(GENERAL_TIME_EXCEED_ERROR, ERROR_500);
            }
            $user->is_otp_verified = true;
            if(!$user->save())
            {
                DB::rollBack();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_500);
            }
            DB::commit();
            return $this->loginUserWithToken($user);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }



}
