<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ]);
    }

    protected function error(string $message = 'Error', int $code = 400, $data = null): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code >= 500 ? 500 : ($code >= 400 ? $code : 400));
    }

    protected function validationError(array $errors, string $message = '参数验证失败'): JsonResponse
    {
        return $this->error($message, 422, ['errors' => $errors]);
    }

    protected function notFound(string $message = '资源未找到'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function paginated($paginator, string $message = 'Success'): JsonResponse
    {
        return $this->success([
            'items' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ], $message);
    }
}
