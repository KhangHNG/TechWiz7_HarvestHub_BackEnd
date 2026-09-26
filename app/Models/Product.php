<?php

namespace App\Models;

use App\Services\CloudinaryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['farmer_id', 'category_id', 'name', 'description', 'price', 'stock_qty', 'image_url'];

    protected $casts = [
        'image_url' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function (Product $product): void {
            if (! $product->isDirty('image_url')) {
                return;
            }

            app(CloudinaryService::class)->deleteRemoved(
                static::imageList($product->getOriginal('image_url')),
                static::imageList($product->image_url),
            );
        });

        static::deleting(function (Product $product): void {
            app(CloudinaryService::class)->deleteUrls(static::imageList($product->image_url));
        });
    }

    /**
     * @return array<int, string>
     */
    public static function imageList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, 'is_string'));
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter($decoded, 'is_string'));
        }

        return [$value];
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
