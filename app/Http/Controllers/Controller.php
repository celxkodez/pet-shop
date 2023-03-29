<?php

namespace App\Http\Controllers;

use App\Http\Resources\V1\UserResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use PHPUnit\Event\Code\Throwable;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Pet Shop API - Swagger Documentation"
 * )
 * @OA\PathItem(path="/api")
 *
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function jsonResponse(int $code, bool $status = true, mixed $data = [], array $extra = [], string $error = null, array $errors = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => $status,
            'data' => $data,
            'error' => $error,
            'errors' => $errors,
            'extra' => $extra,
        ], $code);
    }

    protected function errorResponse(string $error, int $code = 500, Throwable $exception = null, array $errors = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => false,
            'data' => [],
            'error' => $error,
            'errors' => $errors,
            'trace' => is_null($exception) ? [] : $exception->stackTrace(),
        ], $code);
    }

    protected function withErrorHandling(callable $callback)
    {
        try {
            return $callback();
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!', 500);
        }
    }
}
