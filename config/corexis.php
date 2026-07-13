<?php

declare(strict_types=1);

use IvanBaric\Corexis\Resolvers\AppLocaleResolver;
use IvanBaric\Corexis\Resolvers\AuthActorResolver;
use IvanBaric\Corexis\Resolvers\NullTenantResolver;
use IvanBaric\Corexis\Resolvers\RequestSourceResolver;

return [
    'tenancy' => [
        'enabled' => env('COREXIS_TENANCY_ENABLED', false),
        'resolver' => NullTenantResolver::class,
        'id_column' => env('COREXIS_TENANT_ID_COLUMN', 'team_id'),
        'uuid_column' => env('COREXIS_TENANT_UUID_COLUMN', 'tenant_uuid'),
        'type_column' => env('COREXIS_TENANT_TYPE_COLUMN', 'tenant_type'),
    ],

    'locale' => [
        'enabled' => env('COREXIS_LOCALE_ENABLED', false),
        'resolver' => AppLocaleResolver::class,
        'default' => env('APP_LOCALE', 'en'),
        'fallback' => env('APP_FALLBACK_LOCALE', 'en'),
    ],

    'actor' => [
        'enabled' => env('COREXIS_ACTOR_ENABLED', true),
        'resolver' => AuthActorResolver::class,
        'guard' => null,
    ],

    'source' => [
        'enabled' => true,
        'resolver' => RequestSourceResolver::class,
        'default' => 'system',
        'allowed' => [
            'admin',
            'public_form',
            'api',
            'console',
            'queue',
            'system',
            'backfill',
        ],
    ],

    'authorization' => [
        /*
         * Keep false while packages transition to Corexis authorization so
         * existing apps without registered Gates/Velora permissions do not
         * start failing writes. Set true in security-hardened apps.
         */
        'fail_when_missing' => env('COREXIS_AUTHORIZATION_FAIL_WHEN_MISSING', false),
    ],

    'pagination' => [
        'default_items' => (int) env('COREXIS_PAGINATION_DEFAULT_ITEMS', 12),
    ],

    'slug' => [
        'normalizer' => null,
        'normalizer_method' => 'generate',
        'fallback' => 'record',
    ],

    'public' => [
        'test_empty_states' => env('COREXIS_PUBLIC_TEST_EMPTY_STATES', false),
    ],

    'image_uploads' => [
        'default' => [
            'max_file_size_kb' => 6144,
            'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
            'min_width' => null,
            'min_height' => null,
        ],
    ],

    'idempotency' => [
        'enabled' => env('COREXIS_IDEMPOTENCY_ENABLED', true),
        'table' => env('COREXIS_IDEMPOTENCY_TABLE', 'corexis_idempotency_keys'),
        'ttl_minutes' => env('COREXIS_IDEMPOTENCY_TTL_MINUTES', 1440),
    ],
];
