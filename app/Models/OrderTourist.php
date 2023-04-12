<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTourist extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function setBirthAttribute($value) {
        $this->attributes['birth'] = date('Y-m-d', strtotime($value) );
    }
}
