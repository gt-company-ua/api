<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAssistMeContract extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['order_id', 'created_at', 'updated_at', 'payment_id'];
}
