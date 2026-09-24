<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Market extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'address', 'latitude', 'longitude', 'operating_hours', 'is_active'];

    public function farmers()
    {
        return $this->hasMany(Farmer::class);
    }
}
