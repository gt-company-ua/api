<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GreencardCashback extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = ['amount' => 'float'];

    const TRANSPORT_TYPE = ['truck', 'moto', 'trailer', 'default'];
}
