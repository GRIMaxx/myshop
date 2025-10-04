<?php

namespace App\Contracts\Api;

use Illuminate\Http\JsonResponse;
interface ResponseInterface
{
    public function success($data = [], string $message = 'Успешно', int $code = 200): JsonResponse;
    public function error(string $message = 'Ошибка', int $code = 400, $data = []): JsonResponse;
    public function custom($data = [], string $message = '', string $status = 'success', int $code = 200): JsonResponse;
}
