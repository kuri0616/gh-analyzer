<?php

return [
    'app' => [
        'name' => 'GitHub Analyzer',
        'version' => '1.0.0',
    ],
    
    'github' => [
        'api_url' => 'https://api.github.com',
        'timeout' => 30,
        'per_page' => 30,
    ],
    
    'output' => [
        'date_format' => 'Y-m-d H:i:s',
        'table_style' => 'default',
    ],
];