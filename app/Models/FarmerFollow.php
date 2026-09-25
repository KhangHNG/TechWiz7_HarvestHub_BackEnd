<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FarmerFollow extends Model
{
    use SoftDeletes;

    protected $fillable = ['customer_id', 'farmer_id'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }
}
