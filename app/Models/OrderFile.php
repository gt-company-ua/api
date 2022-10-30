<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFile extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['order_id', 'created_at', 'updated_at', 'path'];
}
