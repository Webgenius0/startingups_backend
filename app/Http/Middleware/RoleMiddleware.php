<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {


        if (!Auth::guard('user')->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
            // return $this->error('unauthorize access' , 401);
        }


        if (Auth::guard('user')->user()->role !== $role) {

            return response()->json([
                'status' => 'Forbidden',
                'message' => 'You do not have permission to access this resource.',
                'code' => 403
                ], 403);

        }


        if (!Auth::guard('business')->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
            // return $this->error('unauthorize access' , 401);
        }


        if (Auth::guard('business')->user()->role !== $role) {

            return response()->json([
                'status' => 'Forbidden',
                'message' => 'You do not have permission to access this resource.',
                'code' => 403
                ], 403);

        }




        if (!Auth::guard('admin')->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        if (Auth::guard('admin')->user()->role !== $role) {

            return response()->json([
                'status' => 'Forbidden',
                'message' => 'You do not have permission to access this resource.',
                'code' => 403
                ], 403);
        }



        return $next($request);
    }
}
