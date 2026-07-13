<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$candidates = [
    $root.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'phpunit',
    $root.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'phpunit',
];

$phpunit = null;

foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        $phpunit = $candidate;

        break;
    }
}

if ($phpunit === null) {
    fwrite(STDERR, "PHPUnit binary was not found. Run composer install first.\n");

    exit(1);
}

$command = sprintf(
    '%s %s -c %s',
    escapeshellarg(PHP_BINARY),
    escapeshellarg($phpunit),
    escapeshellarg($root.DIRECTORY_SEPARATOR.'phpunit.xml.dist'),
);

passthru($command, $exitCode);

exit($exitCode);
