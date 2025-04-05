<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    protected $fillable = [
        'asset_id', 'user_id', 'type', 'purchase_price', 'sale_price', 'quantity',
        'exchange', 'buy_commission', 'sell_commission', 'comments', 'status', 'closed_at', 'profitability'
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'created_at' => 'datetime' // Fecha de compra
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function getProfitabilityAttribute()
    {
        if ($this->status === 'closed' && $this->sale_price && $this->purchase_price) {
            return ($this->sale_price - $this->purchase_price) / $this->purchase_price;
        }
        return null;
    }
}