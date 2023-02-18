<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ChangeStatusRequest;
use App\Http\Requests\Api\User\DeleteRequest;
use App\Http\Requests\Api\User\StoreRequest;
use App\Http\Requests\Api\User\UpdatePermissionRequest;
use App\Http\Requests\Api\User\UpdateRequest;
use App\Http\Requests\Api\User\VerifyUserTokenRequest;
use App\Jobs\SendMailJob;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Traits\Api\UserTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    use UserTrait;
    private $user, $pagination;

    public function __construct()
    {
        $this->user = new User();
        $this->permission = new Permission();
        $this->rolePermission = new RolePermission();
        $this->role = new Role();
        $this->pagination = request('page_size', PAGINATE);
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newInstance();
            $user->fill($inputs);
            $user->password = Hash::make($inputs['password']);
            $user->verification_token = generateVerificationToken();
            if ($user->save()) {
                dispatch(new SendMailJob($user->email, 'Account Setup', ['user' => $user, 'password' => $inputs['password']], 'new-user'));
                DB::commit();
                return successDataResponse(GENERAL_SUCCESS_MESSAGE, $this->getUserDetail($user->id));
            }
            DB::rollback();
            return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function update(UpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->where('id', $inputs['id'])->with('role')->first();
            $user->fill($inputs);
            if(!empty($inputs['password']))
            {
                $user->password = Hash::make($inputs['password']);
            }
            if ($user->save()) {
                dispatch(new SendMailJob($user->email, 'Account Setup', ['user' => $user, 'password' => $inputs['password'] ?? null], 'new-user'));
                DB::commit();
                return successDataResponse(GENERAL_UPDATED_MESSAGE, $this->getUserDetail($user->id));
            }
            DB::rollback();
            return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function delete(DeleteRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->where('id', $inputs['id'])->first();
            if (!$user->delete()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successResponse(GENERAL_DELETED_MESSAGE);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function verifyUserToken(VerifyUserTokenRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->where('verification_token', $inputs['token'])->first();
            $user->status = ACTIVE;
            $user->verification_token = NULL;
            if ($user->save()) {
                DB::commit();
                return successResponse(GENERAL_SUCCESS_MESSAGE);
            }
            return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function listing(Request $request)
    {
        $inputs = $request->all();
        $query = $this->user->newQuery()->where('id', '>', 1);

        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['first_name', 'last_name', 'email']);
                searchTable($q, $inputs['search'], ['title'], 'role');
            });
        }
        $users = $query->with(['role'])->paginate(PAGINATE);
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $users);
    }

    private function searchTableByFuzzy($query, $keyword, $filters, $with = null)
    {
        if ($with) {
            $query->orWhereHas($with, function ($q) use ($filters, $keyword) {
                foreach ($filters as $key => $column) {
                    if ($key == 0) {
                        $q->whereFuzzy($column, $keyword);
                    } else {
                        $q->orWhereFuzzy($column, $keyword);
                    }
                }
            });
        } else {
            foreach ($filters as $key => $column) {
                $query->orWhereFuzzy($column, $keyword);
            }
        }
        // dd($query);
        return $query;
    }
    private function searchTableWhereHasByFuzzy($query, $keyword, $filters, $with = null)
    {
        $query->orWhereHas($with, function ($q) use ($filters, $keyword) {
            foreach ($filters as $key => $column) {
                if ($key == 0) {
                    $q->whereFuzzy($column, $keyword);
                } else {
                    $q->orWhereFuzzy($column, $keyword);
                }
            }
        });
        return $query;
    }

    public function changeStatus(ChangeStatusRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->whereId($inputs['id'])->first();
            if($user->status == ACTIVE)
            {
                $user->status = DEACTIVE;
            }else{
                $user->status = ACTIVE;
            }
            if (!$user->save()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successDataResponse(GENERAL_UPDATED_MESSAGE, $user->fresh());
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }


}
