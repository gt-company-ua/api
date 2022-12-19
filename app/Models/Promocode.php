<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocode extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'active' => 'bool',
        'greencard' => 'bool',
        'osago' => 'bool',
        'kasko' => 'bool',
        'vzr' => 'bool',
        'discount' => 'float'
    ];

    public function scopeActive(Builder $query, string $orderType)
    {
        $query->where($orderType, true);
        $query->where('active', true);
        $query->where('expired_at', '>=', now());
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getUsedAttribute()
    {
        return $this->orders->count();
    }
}
