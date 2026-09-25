<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource {
    public function toArray(Request $request): array{
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'created_by' => $this->created_by?->toIso8601String(),
            'deleted_by' => $this->deleted_by?->toIso8601String(),
            'updated_by' => $this->updated_by?->toIso8601String(),
        ];
    }
}
