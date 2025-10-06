<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    // Разрешенные поля для массового присвоения
    protected $fillable = [
        'key',
        'type',
        'visible',
        'searchable',
        'activation',
        'show_popular_first',
        'show_popular_group',
        'show_grouped_group',
        'show_ungrouped_group',
        'show_icon',
        'grouping'
    ];

    // Каст полей
    protected $casts = [
        'visible' 				=> 'boolean',
        'searchable' 			=> 'boolean',
        'show_popular_first' 	=> 'boolean',
        'show_popular_group' 	=> 'boolean',
        'show_grouped_group' 	=> 'boolean',
        'show_ungrouped_group' 	=> 'boolean',
        'show_icon' 			=> 'boolean',
        'grouping' 				=> 'array',
        'sort_order'            => 'integer',    // Сортировка фильтров
    ];

    // Связь с опциями
    public function options()
    {
        return $this->hasMany(FilterOption::class, 'filter_key', 'key');
    }
}
