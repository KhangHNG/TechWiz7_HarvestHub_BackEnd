<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'role',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'password_hash' => 'hashed',
    ];

    // Filament/Laravel auth đọc mật khẩu qua đây thay vì cột "password"
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Tên hiển thị trên giao diện Filament
    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    // Cho phép mọi user vào panel admin (tạm thời; siết lại theo role sau)
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
}
