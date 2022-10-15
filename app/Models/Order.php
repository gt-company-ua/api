<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    const ORDER_TYPES = ['osago', 'zk', 'vzr', 'kasko'];
    const INSURANT_TYPES = ['physical', 'juristic'];
    const DOC_TYPES = ['passport', 'license'];

    use HasFactory;

    protected $guarded = [];

    protected $with = ['transport', 'insurant'];

    protected $casts = [
        'upload_docs' => 'bool',
        'foreign_check' => 'bool',
        'discount_check' => 'bool',
        'price' => 'float',
        'insured_sum' => 'float',
        'gc_plus_price' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($post) {
            $post->uuid = (string) Str::uuid();
        });
    }

    public function transport(): ?HasOne
    {
        return $this->hasOne(OrderTransport::class);
    }

    public function insurant(): ?HasOne
    {
        return $this->hasOne(OrderInsurant::class);
    }
}
