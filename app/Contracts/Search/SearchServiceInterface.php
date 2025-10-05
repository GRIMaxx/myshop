<?php
namespace App\Contracts\Search;

interface SearchServiceInterface
{
    public function getAllConfigArray($showLg = false, $showMd = false): array;



}
