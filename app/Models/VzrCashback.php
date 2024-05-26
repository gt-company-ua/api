<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VzrCashback extends Model
{
    use HasFactory;

    protected $fillable = ['tariff', 'amount'];
}
