<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistMePrice extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = ['price' => 'float', 'old_price' => 'float'];
}
