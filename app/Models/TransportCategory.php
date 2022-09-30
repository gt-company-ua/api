<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function powers()
    {
        return $this->hasMany(TransportPower::class);
    }
}
