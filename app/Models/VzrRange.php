<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VzrRange extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'active' => 'bool',
        'sum' => 'float'
    ];

    public function days(): HasMany
    {
        return $this->hasMany(VzrRangeDay::class);
    }
}
