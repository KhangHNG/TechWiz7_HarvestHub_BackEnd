<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class CloudinaryService
{
    public function upload(UploadedFile $file): string
    {
        $result = $this->client()->uploadApi()->upload($file->getRealPath(), [
            'folder' => config('services.cloudinary.folder', 'products'),
            'resource_type' => 'image',
        ]);

        $url = $result['secure_url'] ?? null;

        if (! is_string($url) || $url === '') {
            throw new RuntimeException('Không tải được ảnh lên Cloudinary.');
        }

        return $url;
    }

    /**
     * @param  array<int, mixed>  $previous
     * @param  array<int, mixed>  $current
     */
    public function deleteRemoved(array $previous, array $current): void
    {
        $currentUrls = array_values(array_filter($current, 'is_string'));

        foreach ($this->normalize($previous) as $url) {
            if (! in_array($url, $currentUrls, true)) {
                $this->deleteByUrl($url);
            }
        }
    }

    /**
     * @param  array<int, mixed>  $urls
     */
    public function deleteUrls(array $urls): void
    {
        foreach ($this->normalize($urls) as $url) {
            $this->deleteByUrl($url);
        }
    }

    public function deleteByUrl(?string $url): void
    {
        $publicId = $this->publicIdFromUrl($url);

        if ($publicId === null) {
            return;
        }

        try {
            $this->client()->uploadApi()->destroy($publicId);
        } catch (\Throwable) {
            // Ảnh có thể đã bị xóa trước đó.
        }
    }

    public function publicIdFromUrl(?string $url): ?string
    {
        if (! is_string($url) || ! str_contains($url, '/image/upload/')) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path)) {
            return null;
        }

        $after = strstr($path, '/image/upload/');

        if ($after === false) {
            return null;
        }

        $segments = explode('/', substr($after, strlen('/image/upload/')));
        $kept = [];

        foreach ($segments as $segment) {
            if ($segment === '' || preg_match('/^v\d+$/', $segment) || str_contains($segment, ',')) {
                continue;
            }

            $kept[] = $segment;
        }

        $publicId = preg_replace('/\.[^.]+$/', '', implode('/', $kept));

        return is_string($publicId) && $publicId !== '' ? $publicId : null;
    }

    /**
     * @param  array<int, mixed>  $urls
     * @return array<int, string>
     */
    private function normalize(array $urls): array
    {
        return array_values(array_filter($urls, 'is_string'));
    }

    private function client(): Cloudinary
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        if (! is_string($cloudName) || $cloudName === '' || ! is_string($apiKey) || $apiKey === '' || ! is_string($apiSecret) || $apiSecret === '') {
            throw new RuntimeException('Thiếu cấu hình Cloudinary.');
        }

        return new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
        ]);
    }
}
