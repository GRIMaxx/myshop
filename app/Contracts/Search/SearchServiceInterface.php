<?php
namespace App\Contracts\Search;

interface SearchServiceInterface
{
    // Получить настройки по умолчанию
    public function getAllConfigArray($showLg = false, $showMd = false): array;



}
