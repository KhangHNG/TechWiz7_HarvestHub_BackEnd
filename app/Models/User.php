<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password_hash',
        'address',
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
}
