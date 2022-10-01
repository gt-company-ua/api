<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KaskoInsuranceValue extends Model
{
    use HasFactory;

    public function prices()
    {
        return $this->hasMany(KaskoPrice::class);
    }
}
