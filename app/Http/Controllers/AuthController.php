<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        $token = auth()->refresh();
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        $expires = null;

        try {
            $apiGuard = auth('api');
            $defaultGuard = auth();

            if (is_callable([$apiGuard, 'factory'])) {
                $expires = $apiGuard->factory()->getTTL() * 60;
            } elseif (is_callable([$defaultGuard, 'factory'])) {
                $expires = $defaultGuard->factory()->getTTL() * 60;
            }
        } catch (\Throwable $e) {
            $expires = null;
        }

        $response = response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expires
        ]);                

        return $response;
    }
}
