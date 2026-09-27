<?php

namespace App\Models;

use App\Services\CloudinaryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Farmer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'market_id', 'business_name', 'description', 'rating', 'is_accepting_orders', 'cover_url'];

    protected static function booted(): void
    {
        static::updating(function (Farmer $farmer): void {
            if (! $farmer->isDirty('cover_url')) {
                return;
            }

            $original = $farmer->getOriginal('cover_url');

            if (! is_string($original) || $original === '' || $original === $farmer->cover_url) {
                return;
            }

            app(CloudinaryService::class)->deleteByUrl($original);
        });

        static::deleting(function (Farmer $farmer): void {
            app(CloudinaryService::class)->deleteByUrl($farmer->cover_url);
        });
    }

    protected $casts = [
        'is_accepting_orders' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
