<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    const ORDER_TYPES = ['osago', 'zk', 'vzr', 'kasko'];
    const INSURANT_PHYSICAL = 'physical';
    const INSURANT_JURISTIC = 'juristic';
    const INSURANT_TYPES = [self::INSURANT_PHYSICAL, self::INSURANT_JURISTIC];
    const DOC_PASSPORT = 'passport';
    const DOC_LICENSE = 'license';
    const DOC_TYPES = [self::DOC_PASSPORT, self::DOC_LICENSE];
    const DOC_NAMES = [
        self::DOC_PASSPORT => 'Паспорт',
        self::DOC_LICENSE => 'Водительское удостоверение'
    ];
    const DOC_API_ID = [
        self::DOC_PASSPORT => 1,
        self::DOC_LICENSE => 5
    ];

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

    public function contract(): ?HasOne
    {
        return $this->hasOne(OrderContract::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(OrderFile::class);
    }
}
