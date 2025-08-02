<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a successful JSON response
     * 
     * Creates a standardized success response with optional data payload.
     * Used across controllers to maintain consistent API response format.
     *
     * @param mixed $data Optional data to include in response
     * @param string $message Success message (default: 'Success')
     * @param int $statusCode HTTP status code (default: 200)
     * @return JsonResponse Standardized success response
     */
    protected function success($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return an error JSON response
     * 
     * Creates a standardized error response with optional error details.
     * Used across controllers and middleware for consistent error handling.
     *
     * @param string $message Error message (default: 'Error')
     * @param int $statusCode HTTP status code (default: 400)
     * @param mixed $errors Optional error details or validation errors
     * @return JsonResponse Standardized error response
     */
    protected function error(string $message = 'Error', int $statusCode = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}
