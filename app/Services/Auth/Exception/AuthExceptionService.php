<?php

namespace App\Services\Auth\Exception;

use Exception;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthExceptionService
{
    protected Exception $exception;
    protected string $message = 'Unauthorized';
    protected int $status = Response::HTTP_UNAUTHORIZED;

    public function __construct(Exception $e)
    {
        $this->exception = $e;
    }

    /**
     * Normalize the exception into a typed responder (returns $this for convenience).
     */
    public function getType(): self
    {
        $e = $this->exception;

        if ($e instanceof TokenExpiredException) {
            $this->message = 'Token expired';
            $this->status = Response::HTTP_UNAUTHORIZED;
        } elseif ($e instanceof TokenInvalidException) {
            $this->message = 'Token invalid';
            $this->status = Response::HTTP_UNAUTHORIZED;
        } elseif ($e instanceof JWTException) {
            $this->message = 'Token not provided or invalid';
            $this->status = Response::HTTP_UNAUTHORIZED;
        } else {
            // Fallback: keep original message but avoid leaking internal details in production
            $this->message = $e->getMessage() ?: 'Unauthorized';
            $this->status = Response::HTTP_UNAUTHORIZED;
        }

        return $this;
    }

    /**
     * Return a JsonResponse compatible with how middleware expects to send auth errors.
     */
    public function response(): JsonResponse
    {
        return response()->json([
            'error' => $this->message,
        ], $this->status);
    }
}
