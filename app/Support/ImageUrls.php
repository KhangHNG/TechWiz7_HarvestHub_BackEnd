<?php

namespace App\Support;

/**
 * Builds smaller delivery URLs for photos the app already stores.
 * Wikimedia and Cloudinary generate the smaller file. Other addresses stay as they are.
 */
class ImageUrls
{
    /** Width of a photo on a card, cart row, or assistant chip. Wikimedia only serves some widths; 640 is rejected. */
    public const CARD_WIDTH = 500;

    /** Width of a photo on the product details page. */
    public const GALLERY_WIDTH = 960;

    public static function resize(string $url, int $width): string
    {
        $url = trim($url);
        if ($url === '' || $width < 1) {
            return $url;
        }

        return self::cloudinary($url, $width)
            ?? self::wikimedia($url, $width)
            ?? $url;
    }

    private static function cloudinary(string $url, int $width): ?string
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ($parts['host'] ?? '') !== 'res.cloudinary.com') {
            return null;
        }

        $path = $parts['path'] ?? '';
        $marker = '/image/upload/';
        $position = strpos($path, $marker);
        if ($position === false) {
            return null;
        }

        $prefix = substr($path, 0, $position + strlen($marker));
        $rest = substr($path, $position + strlen($marker));
        $rest = preg_replace('#^f_auto,q_auto,w_\d+,c_limit/#', '', $rest) ?? $rest;
        $transform = "f_auto,q_auto,w_{$width},c_limit/";
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';

        return ($parts['scheme'] ?? 'https').'://'.$parts['host'].$prefix.$transform.$rest.$query;
    }

    private static function wikimedia(string $url, int $width): ?string
    {
        $parts = parse_url($url);
        if (! is_array($parts)) {
            return null;
        }

        $host = $parts['host'] ?? '';
        if (! in_array($host, ['upload.wikimedia.org', 'thumb.wikimedia.org'], true)) {
            return null;
        }

        $path = $parts['path'] ?? '';

        if (preg_match('#^(.*?/wikipedia/commons/thumb/[^/]+/[^/]+/)([^/]+)/(.+)$#', $path, $matches) === 1) {
            $file = $matches[2];
            if (preg_match('/\.(tif|tiff)$/i', $file) === 1) {
                return 'https://upload.wikimedia.org'.$matches[1].$file.'/'.$width.'px-'.$file.'.jpg';
            }

            $name = preg_replace('/\d+px-/', $width.'px-', $matches[3], 1) ?? $matches[3];

            return 'https://upload.wikimedia.org'.$matches[1].$file.'/'.$name;
        }

        if (preg_match('#^/wikipedia/commons/([^/]+)/([^/]+)/([^/]+)$#', $path, $matches) === 1) {
            // A 960px PNG thumb of a full-size PNG can be heavier than the file itself.
            if ($width >= self::GALLERY_WIDTH) {
                return $url;
            }

            $file = $matches[3];
            $suffix = preg_match('/\.(tif|tiff)$/i', $file) === 1 ? '.jpg' : '';

            return 'https://upload.wikimedia.org/wikipedia/commons/thumb/'
                .$matches[1].'/'.$matches[2].'/'.$file.'/'
                .$width.'px-'.$file.$suffix;
        }

        return null;
    }
}
