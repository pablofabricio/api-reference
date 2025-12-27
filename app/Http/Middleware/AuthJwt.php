<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\Auth\Exception\AuthExceptionService;
use Illuminate\Support\Facades\Auth;

class AuthJwt extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if ($user) {
                Auth::setUser($user);
            }
        }
        catch (\Exception $e) {
            $jwtException = (new AuthExceptionService($e))->getType(); 
            return $jwtException->response();
        }

        return $next($request);
    }
}
