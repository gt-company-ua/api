<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VignetteOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function cars(): HasMany
    {
        return $this->hasMany(VignetteOrderCar::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(VignetteProduct::class, 'vignette_product_id', 'id');
    }
}
