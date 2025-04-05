<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'portfolio_id', 'name', 'symbol', 'current_price', 
        'lowest_price_bought', 'highest_price_reached', 'monitoring_point', 
        'comments'
    ];

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function strategy()
    {
        return $this->hasOne(Strategy::class);
    }
}