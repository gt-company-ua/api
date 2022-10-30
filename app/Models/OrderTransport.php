<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTransport extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $with = ['power'];
    protected $hidden = ['id', 'order_id', 'created_at', 'updated_at'];

    public function power(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TransportPower::class, 'transport_power_id', 'id');
    }
}
