<?php

declare(strict_types=1);

return [
    'result' => [
        'success' => 'The operation completed successfully.',
        'error' => 'The operation could not be completed.',
    ],
    'authorization' => [
        'denied' => 'You are not authorized to perform this action.',
    ],
    'validation' => [
        'required' => 'Required field',
    ],

    'concurrency' => [
        'stale_model' => 'This record was changed by someone else. Refresh the data before saving.',
    ],
    'idempotency' => [
        'processing' => 'A request with the same idempotency key is already being processed.',
        'replayed' => 'This request has already been processed.',
    ],
    'cookie_consent' => [
        'aria_label' => 'Cookie notice',
        'title' => 'This website uses cookies',
        'message' => 'We use necessary cookies for the website to work properly and anonymous visit statistics to understand which content is read most often.',
        'accept' => 'OK',
        'policy' => 'Learn more',
    ],
];
