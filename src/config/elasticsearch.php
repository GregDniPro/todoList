<?php

declare(strict_types=1);

return [
    'host' => env('ELASTICSEARCH_HOST'),
    'user' => env('ELASTICSEARCH_USER'),
    'password' => env('ELASTICSEARCH_PASS'),
    'cloud_id' => env('ELASTICSEARCH_CLOUD_ID'),
    'api_key' => env('ELASTICSEARCH_API_KEY'),
    'queue' => [
        'timeout' => env('SCOUT_QUEUE_TIMEOUT', 15),
    ],
    'indices' => [
        'mappings' => [
            'default' => [
                'properties' => [
                    'id' => [
                        'type' => 'keyword',
                    ],
                    'title' => [
                        'type' => 'text',
                        'analyzer' => 'standard',
                    ],
                    'description' => [
                        'type' => 'text',
                        'analyzer' => 'standard',
                    ],
                ],
            ],
        ],
        'settings' => [
            'default' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ],
        ],
    ],
];
