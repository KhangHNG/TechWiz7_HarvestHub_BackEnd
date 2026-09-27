<?php

namespace App\Models;

use App\Services\CloudinaryService;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements FilamentUser, HasName, JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password_hash',
        'address',
        'city',
        'district',
        'capital',
        'avatar_url',
        'role',
        'email_verified_at',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'password_hash' => 'hashed',
        'email_verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (User $user): void {
            if (! $user->isDirty('avatar_url')) {
                return;
            }

            $original = $user->getOriginal('avatar_url');

            if (! is_string($original) || $original === '' || $original === $user->avatar_url) {
                return;
            }

            app(CloudinaryService::class)->deleteByUrl($original);
        });

        static::deleting(function (User $user): void {
            app(CloudinaryService::class)->deleteByUrl($user->avatar_url);
        });
    }

    // Filament/Laravel auth reads the password here instead of a "password" column
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Display name on the Filament UI
    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    // Allow every user into the admin panel (temporary; tighten by role later)
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
        ];
    }

    public function farmer()
    {
        return $this->hasOne(Farmer::class);
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(FilamentDatabaseNotification::class, 'notifiable')->latest();
    }
}
