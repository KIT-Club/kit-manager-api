<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permissions): Response
    {
        if (is_null($permissions) || empty($permissions))  // if permissions is null then skip checking
            return $next($request);

        $userRole = Auth::user()->role;
        $userPermissions = $userRole->permissions;

        if ($userRole->is_admin) // if  the user is admin then skip checking
            return $next($request);

        // permissions checking
        if (is_null($userPermissions) || !in_array($permissions, $userPermissions))
            return abort(403); // Forbidden

        return $next($request);
    }
}
