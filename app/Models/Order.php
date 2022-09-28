<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    const ORDER_TYPES = ['osago', 'zk', 'vzr', 'kasko'];
    const INSURANT_TYPES = ['physical', 'juristic'];

    use HasFactory;

    protected $guarded = [];

    public function transport(): ?HasOne
    {
        return $this->hasOne(OrderTransport::class);
    }

    public function insurant(): ?HasOne
    {
        return $this->hasOne(OrderInsurant::class);
    }
}
