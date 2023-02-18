<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use App\Models\RolePermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $requestUri = '/'.$request->path();
        $path = head(explode('/', last(explode('/api/', $requestUri))));
        $lastPath = last(explode('/', last(explode('/api/', $requestUri))));
        if (is_string($path)) {
            $rolPermissions = Auth::User()->role;
            if (count($rolPermissions->permissions)) {

                $permission = Permission::whereModule($path)->first();
                if ($permission) {
                    $action = $this->checkRouteAction($lastPath);
                    if (RolePermission::wherePermissionId($permission->id)->whereRoleId($rolPermissions->id)->whereAction($action)->exists()) {
                        return $next($request);
                    } else return response()->json(['success' => false, 'message' => 'Access Denied'], ERROR_403);
                } else return response()->json(['success' => false, 'message' => 'Access Denied'], ERROR_403);
            } else return response()->json(['success' => false, 'message' => 'Access Denied'], ERROR_403);
        }
        return response()->json(['success' => false, 'message' => 'Access Denied'], ERROR_403);
    }



    private function checkRouteAction($lastPath)
    {
        $writeRequests = [
            'store', 'update', 'delete', 'change-status', 'details', 'update-permissions', 'update-sorting', 'change-status', 'change-buy-now', 'remove-image',
            'delete-merchant', 'update-merchant-sorting', 'image-update', 'clone', 'change-buying-options', 'delete-mailbox', 'delete-attribute', 'update-discount-type'
            ];
        $readRequests = ['listing', 'products', 'stats', 'customer-total-listing', 'show', 'work-order-listing', 'charts', 'detail', 'merchant-listing', 'filter-listing', 'categories'];
        if (in_array($lastPath, $writeRequests)) {
            return ROLE_ACTION_WRITE;
        } else if (in_array($lastPath, $readRequests)) {
            return ROLE_ACTION_READ;
        }
    }

}
