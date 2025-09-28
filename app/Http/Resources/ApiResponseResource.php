<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponseResource extends JsonResource
{
    protected bool $success;
    protected ?string $message;
    protected ?array $errors;
    protected int $statusCode;

    public function __construct($resource, bool $success = true, ?string $message = null, ?array $errors = null, int $statusCode = 200)
    {
        parent::__construct($resource);
        $this->success = $success;
        $this->message = $message;
        $this->errors = $errors;
        $this->statusCode = $statusCode;
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $response = [
            'success' => $this->success,
        ];

        if ($this->message) {
            $response['message'] = $this->message;
        }

        if ($this->errors) {
            $response['errors'] = $this->errors;
        }

        if ($this->resource !== null) {
            $response['data'] = $this->resource;
        }

        return $response;
    }

    /**
     * Create a success response.
     */
    public static function success($data = null, ?string $message = null, int $statusCode = 200): JsonResponse
    {
        return (new static($data, true, $message, null, $statusCode))
            ->response()
            ->setStatusCode($statusCode);
    }

    /**
     * Create an error response.
     */
    public static function error(?string $message = null, ?array $errors = null, int $statusCode = 400): JsonResponse
    {
        return (new static(null, false, $message, $errors, $statusCode))
            ->response()
            ->setStatusCode($statusCode);
    }

    /**
     * Create a not found response.
     */
    public static function notFound(?string $message = 'Resource not found'): JsonResponse
    {
        return static::error($message, null, 404);
    }

    /**
     * Create a validation error response.
     */
    public static function validationError(array $errors, ?string $message = 'Validation failed'): JsonResponse
    {
        return static::error($message, $errors, 422);
    }
}





