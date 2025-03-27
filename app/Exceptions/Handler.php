<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (Throwable $e) {
            return $this->handleException($e);
        });
    }

    protected function handleException(Throwable $e): JsonResponse
    {
        $statusCode = $this->getStatusCode($e);
        $message = $e->getMessage();
    
        return response()->json(['message' => $message], $statusCode);
    }
    
    protected function getStatusCode(Throwable $e): int
    {
        return match (true) {
            $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException,
            $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException => 404,
    
            $e instanceof \Illuminate\Validation\ValidationException => 422,
    
            $e instanceof \Illuminate\Auth\AuthenticationException => 401,
            $e instanceof \Illuminate\Auth\Access\AuthorizationException,
            $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException => 403,
    
            $e instanceof \Illuminate\Database\QueryException => 500,
    
            default => empty($e->getCode()) || is_string($e->getCode()) ? 500 : (int) $e->getCode(),
        };
    }
}
