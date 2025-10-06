<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilterOptionTranslation extends Model
{
    public $timestamps = false; // Отключаем временные метки, если не нужны
    protected $fillable = ['name', 'description'];
}
