<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Strategy extends Model
{
    protected $fillable = ['asset_id', 'buy_threshold', 'sell_threshold'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}