<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource {
    public function toArray(Request $request): array{
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'image_url' => $this->absoluteImageUrl(),
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'created_by' => $this->created_by,
            'deleted_by' => $this->deleted_by,
            'updated_by' => $this->updated_by,
            'sample_image_url' => $this->when(
                $request->boolean('with_sample_image'),
                $this->sample_image_url,
            ),
        ];
    }

    /** Uploaded pictures are full links; seeded ones are public paths. */
    private function absoluteImageUrl(): ?string
    {
        $url = $this->image_url;

        if (! is_string($url) || $url === '') {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }
}
