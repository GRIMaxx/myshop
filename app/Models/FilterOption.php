<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class FilterOption extends Model implements TranslatableContract
{
    use Translatable;

    public $translatedAttributes = ['name', 'description'];
    public $translationModel = FilterOptionTranslation::class;

    protected $fillable = [
        'filter_key',
        'option_key',
        'default',
        'count',
        'popular',
        'icon',
        'color',
        'start_min',
        'start_max',
        'min',
        'max',
        'step',
        'tooltip_prefix',
        'tooltip_suffix',
        'unit',
        'metadata',
        'group'
    ];

    protected $casts = [
        'default' => 'boolean',
        'popular' => 'boolean',
        'metadata' => 'array'
    ];
}
