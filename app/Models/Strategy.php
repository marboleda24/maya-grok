<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Strategy extends Model
{
    protected $fillable = [
        'asset_id', 'name', 'buy_threshold', 'sell_threshold', 
        'techo_threshold', 'minimum_open_operations', 'comments'
    ];

    protected $casts = [
        'buy_threshold' => 'decimal:2',
        'sell_threshold' => 'decimal:2',
        'techo_threshold' => 'decimal:2',
        'minimum_open_operations' => 'integer'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}