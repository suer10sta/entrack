<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckEnterprisePermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!$request->user()->hasPermissionInEnterprise($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
