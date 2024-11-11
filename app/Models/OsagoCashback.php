<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OsagoCashback extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = ['amount' => 'float'];
}
