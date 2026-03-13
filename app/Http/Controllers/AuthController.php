<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $guard = auth('api');
        
        if (! $token = $guard->attempt($credentials)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        JWTAuth::parseToken()->invalidate();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        $token = JWTAuth::parseToken()->refresh();
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        $ttl = config('jwt.ttl');
        $expires = is_numeric($ttl) ? ((int) $ttl) * 60 : null;

        $response = response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expires
        ]);                

        return $response;
    }
}
