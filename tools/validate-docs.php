<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$index = $root.DIRECTORY_SEPARATOR.'AGENTS.md';
$markdownFiles = [$index, $root.DIRECTORY_SEPARATOR.'README.md'];
$docs = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $root.DIRECTORY_SEPARATOR.'docs',
        FilesystemIterator::SKIP_DOTS,
    ),
);

foreach ($docs as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'md') {
        $markdownFiles[] = $file->getPathname();
    }
}

sort($markdownFiles);

$errors = [];
$indexedDocuments = [];
$mojibakeMarkers = ['Ã', 'Ä', 'Å', 'â€', '�'];

foreach ($markdownFiles as $file) {
    $content = file_get_contents($file);

    if ($content === false) {
        $errors[] = relativePath($root, $file).': file could not be read.';

        continue;
    }

    $relativeFile = relativePath($root, $file);

    if (str_starts_with($content, "\xEF\xBB\xBF")) {
        $errors[] = $relativeFile.': UTF-8 BOM is not allowed.';
    }

    if (preg_match('//u', $content) !== 1) {
        $errors[] = $relativeFile.': invalid UTF-8.';
    }

    foreach ($mojibakeMarkers as $marker) {
        if (str_contains($content, $marker)) {
            $errors[] = $relativeFile.': possible mojibake marker '.$marker.'.';
        }
    }

    preg_match_all('/^# .+$/m', $content, $headings);

    if (count($headings[0]) !== 1) {
        $errors[] = $relativeFile.': expected exactly one H1 heading.';
    }

    preg_match_all('/\[[^\]]*\]\(([^)]+)\)/', $content, $links);

    foreach ($links[1] as $target) {
        $target = trim($target);

        if (
            $target === ''
            || str_starts_with($target, '#')
            || preg_match('/^[a-z][a-z0-9+.-]*:/i', $target) === 1
        ) {
            continue;
        }

        $target = rawurldecode(explode('#', $target, 2)[0]);
        $resolved = canonicalPath(dirname($file).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $target));

        if (! is_file($resolved)) {
            $errors[] = $relativeFile.': broken link '.$target.'.';

            continue;
        }

        if ($file === $index && str_starts_with(relativePath($root, $resolved), 'docs/')) {
            $indexedDocuments[$resolved] = true;
        }
    }
}

foreach ($markdownFiles as $file) {
    if (! str_starts_with(relativePath($root, $file), 'docs/')) {
        continue;
    }

    $resolved = canonicalPath($file);

    if (! isset($indexedDocuments[$resolved])) {
        $errors[] = relativePath($root, $file).': document is not linked from AGENTS.md.';
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Documentation validation failed:\n");

    foreach (array_unique($errors) as $error) {
        fwrite(STDERR, ' - '.$error."\n");
    }

    exit(1);
}

fwrite(STDOUT, sprintf(
    "Documentation validation passed (%d Markdown files).\n",
    count($markdownFiles),
));

function canonicalPath(string $path): string
{
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $prefix = '';

    if (preg_match('/^[A-Za-z]:'.preg_quote(DIRECTORY_SEPARATOR, '/').'/', $path) === 1) {
        $prefix = substr($path, 0, 3);
        $path = substr($path, 3);
    } elseif (str_starts_with($path, DIRECTORY_SEPARATOR)) {
        $prefix = DIRECTORY_SEPARATOR;
        $path = ltrim($path, DIRECTORY_SEPARATOR);
    }

    $segments = [];

    foreach (explode(DIRECTORY_SEPARATOR, $path) as $segment) {
        if ($segment === '' || $segment === '.') {
            continue;
        }

        if ($segment === '..') {
            array_pop($segments);

            continue;
        }

        $segments[] = $segment;
    }

    return $prefix.implode(DIRECTORY_SEPARATOR, $segments);
}

function relativePath(string $root, string $path): string
{
    $root = rtrim(canonicalPath($root), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    $path = canonicalPath($path);

    return str_replace(DIRECTORY_SEPARATOR, '/', str_starts_with($path, $root)
        ? substr($path, strlen($root))
        : $path);
}
