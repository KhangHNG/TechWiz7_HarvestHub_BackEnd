<?php

namespace App\Http\Resources;

use App\Support\SeedImages;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageUrl = null;
        if ($this->relationLoaded('product') && $this->product) {
            $images = $this->product->image_url;
            if (is_array($images) && count($images) > 0 && is_string($images[0])) {
                $imageUrl = SeedImages::absoluteUrl($images[0]);
            }
        }
        if (! $imageUrl) {
            $imageUrl = SeedImages::absoluteUrl(SeedImages::guessForProduct((string) $this->product_name));
        }

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'line_total' => $this->line_total,
            'image_url' => $imageUrl,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

