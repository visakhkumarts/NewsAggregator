<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
    {
        // Handle API requests
        if ($request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions.
     */
    protected function handleApiException(Request $request, Throwable $e): JsonResponse
    {
        // Log the exception for debugging
        Log::error('API Exception', [
            'message' => $e->getMessage(),
            'type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
        ]);

        // Handle validation exceptions
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Handle not found exceptions
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found'
            ], 404);
        }

        // Handle method not allowed exceptions
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
        }

        // Handle authentication exceptions
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide a valid token.'
            ], 401);
        }

        if ($e instanceof UnauthorizedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide a valid token.'
            ], 401);
        }

        // Handle authorization exceptions
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
        }

        // Handle Sanctum missing ability exceptions
        if ($e instanceof MissingAbilityException) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
        }

        // Handle other exceptions
        $statusCode = 500;
        if (method_exists($e, 'getCode') && $e->getCode() > 0) {
            $statusCode = $e->getCode();
        }
        
        return response()->json([
            'success' => false,
            'message' => $statusCode === 500 ? 'Internal server error' : $e->getMessage(),
            'error' => config('app.debug') ? $e->getMessage() : null
        ], $statusCode);
    }
}
