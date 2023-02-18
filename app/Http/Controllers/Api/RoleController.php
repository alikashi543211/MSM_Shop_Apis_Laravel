<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Role\UpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Traits\Api\RoleTrait;
use App\Traits\Api\UserTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    use UserTrait, RoleTrait;
    private $user, $pagination, $permission;

    public function __construct()
    {
        $this->user = new User();
        $this->permission = new Permission();
        $this->rolePermission = new RolePermission();
        $this->role = new Role();
        $this->pagination = request('page_size', PAGINATE);
    }

    public function listing()
    {
        $roles = $this->role->newQuery()->get();
        foreach ($roles as $key => $role) {
            $role->permissions = $this->getFormattedPermissions($role);
        }
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $roles);
    }

    public function update(UpdateRequest $request)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if (!$this->updateRolesWithPermissions($inputs)) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successResponse(GENERAL_SUCCESS_MESSAGE, SUCCESS_200);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function permissionListing(Request $request)
    {
        $permissions = $this->permission->newQuery()->select(['id', 'module'])->get();
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $permissions);
    }
}
