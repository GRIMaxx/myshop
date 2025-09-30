<?php
// https://github.com/ryangjchandler/blade-cache-directive
return [

    'enabled' => env('BLADE_CACHE_DIRECTIVE_ENABLED', true),

    'ttl' => env('BLADE_CACHE_DIRECTIVE_TTL', 3600),         //По умолчанию TTL = 3600 секунд (1 час)
];
