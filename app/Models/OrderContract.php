<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderContract extends Model
{
    use HasFactory;

    const STATUS_CONTRACT_NOT_SENT = 'not_sent';
    const STATUS_CONTRACT_SENT = 'sent';
    const STATUS_CONTRACT_ERROR = 'error';

    protected $guarded = [];
    protected $hidden = ['id', 'order_id', 'created_at', 'updated_at'];
}
