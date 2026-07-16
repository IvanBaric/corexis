<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

final class PublicUrl
{
    /** @var list<string> */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    public function sanitize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $url = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($url === '' || preg_match('/[\x00-\x1F\x7F]/u', $url)) {
            return null;
        }

        if (str_starts_with($url, '//') || str_contains($url, '\\')) {
            return null;
        }

        if (str_starts_with($url, '#') || str_starts_with($url, '/') || str_starts_with($url, './') || str_starts_with($url, '../')) {
            return $url;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! is_string($scheme) || ! in_array(strtolower($scheme), self::ALLOWED_SCHEMES, true)) {
            return null;
        }

        if (in_array(strtolower($scheme), ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return $url;
    }
}
