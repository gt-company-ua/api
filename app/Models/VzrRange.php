<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VzrRange extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function days()
    {
        return $this->hasMany(VzrRangeDay::class);
    }
}
