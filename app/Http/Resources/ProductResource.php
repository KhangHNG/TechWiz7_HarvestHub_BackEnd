<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform resource thành một mảng JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'price'       => $this->price,
            'image_url'   => $this->image_url,
            'description' => $this->description,
            'farmer_id'   => $this->farmer_id,
            'category_id' => $this->category_id,
            'stock_qty' => $this->stock_qty,

            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'created_by' => $this->created_by,
            'deleted_by' => $this->deleted_by,
            'updated_by' => $this->updated_by,
        ];
    }
}
