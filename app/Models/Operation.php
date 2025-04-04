<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    protected $fillable = [
        'asset_id',
        'purchase_price',
        'quantity',
        'sale_price',
        'status',
        'closed_at',
        'exchange',
        'comments',
        'buy_commission',
        'sell_commission'
    ];

    protected $dates = ['closed_at']; // Para tratar closed_at como instancia de Carbon

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}