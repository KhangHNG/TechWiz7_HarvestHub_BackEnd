<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmerWalletTransaction extends Model
{
    protected $fillable = ['farmer_id', 'order_id', 'amount', 'type'];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
