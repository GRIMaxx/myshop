<?php
/**
 * Централизованный формат JSON-ответа
 * $products = Product::all();
 * return $this->response->success($products, 'Список товаров');
 * return app('apiResponse')->success(['id' => 1]);
 * return app('apiResponse')->error('Ошибка при обработке', 422);
 **/
namespace App\Services\Api;

use App\Contracts\Api\ResponseInterface;
use Illuminate\Http\JsonResponse;

class ResponseService implements ResponseInterface
{
    public function success($data = [], string $message = 'Успешно', int $code = 200): JsonResponse
    {
        return $this->custom($data, $message, 'success', $code);
    }

    public function error(string $message = 'Ошибка', int $code = 400, $data = []): JsonResponse
    {
        return $this->custom($data, $message, 'error', $code);
    }

    public function custom($data = [], string $message = '', string $status = 'success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
}
