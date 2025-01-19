<?php

namespace App\Models;

use App\Services\api\Ingo;
use App\Services\api\TasIns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    const ORDER_TYPE_GC = 'greencard';
    const ORDER_TYPE_OSAGO = 'osago';
    const ORDER_TYPE_VZR = 'vzr';
    const ORDER_TYPE_KASKO = 'kasko';
    const ORDER_TYPES = [self::ORDER_TYPE_OSAGO, self::ORDER_TYPE_GC, self::ORDER_TYPE_VZR, self::ORDER_TYPE_KASKO];
    const INSURANT_PHYSICAL = 'physical';
    const INSURANT_JURISTIC = 'juristic';
    const INSURANT_TYPES = [self::INSURANT_PHYSICAL, self::INSURANT_JURISTIC];
    const DOC_PASSPORT = 'passport';
    const DOC_FOREIGN_PASSPORT = 'foreignPassport';
    const DOC_LICENSE = 'license';
    const DOC_ID = 'id';
    const DOC_TYPES = [self::DOC_PASSPORT, self::DOC_LICENSE, self::DOC_ID];
    const DOC_NAMES = [
        self::DOC_PASSPORT => 'Паспорт',
        self::DOC_FOREIGN_PASSPORT => 'Закордонний паспорт',
        self::DOC_LICENSE => 'Водительское удостоверение',
        self::DOC_ID => 'ID'
    ];
    const DOC_API_ID = [
        self::DOC_PASSPORT => 1,
        self::DOC_LICENSE => 5
    ];

    const DOC_TAS_API_ID = [
        self::DOC_PASSPORT => 1,
        self::DOC_FOREIGN_PASSPORT => 3,
        self::DOC_ID => 2,
        self::DOC_LICENSE => 5
    ];

    const DOC_SALAMANDRA_API_ID = [
        self::DOC_PASSPORT => 'Passport',
        self::DOC_LICENSE => 'DriverLicense',
        self::DOC_ID => 'ID',
    ];

    const TRIP_COUNTRY_SNG = 'sng';
    const TRIP_COUNTRY_EU = 'eu';
    const TRIP_COUNTRIES = [self::TRIP_COUNTRY_EU, self::TRIP_COUNTRY_SNG];

    const TERRITORY_EU = 'europe';
    const TERRITORY_WORLD = 'world';
    const TERRITORIES = [self::TERRITORY_EU, self::TERRITORY_WORLD];

    const SPORTS = ['none', 'active', 'pro'];
    const TARGETS = ['rest', 'work', 'learn', 'sport'];
    const VZR_INSURED_SUMS = [30000, 50000, 75000];

    const PARTNER_VIGNETTE = 'vignette';
    const PARTNERS = [self::PARTNER_VIGNETTE];

    use HasFactory;

    protected $guarded = [];

    protected $with = ['transport', 'insurant', 'assist', 'tourists'];
    protected $hidden = [
        'id', 'send_sms', 'contract_response', 'crm_contact_id', 'crm_deal_id',
        'crm_car_id', 'ga_id', 'promocode_id', 'code'
    ];

    const INSURANCE_COMPANIES = [Ingo::API_NAME, TasIns::API_NAME];

    protected $casts = [
        'use_scoring' => 'bool',
        'draft' => 'bool',
        'draft_sent' => 'bool',
        'use_as_taxi' => 'bool',
        'is_pu' => 'bool',
        'is_dms' => 'bool',
        'dont_call' => 'bool',
        'cashback_to_vsu' => 'bool',
        'upload_docs' => 'bool',
        'foreign_check' => 'bool',
        'discount_check' => 'bool',
        'with_covid' => 'bool',
        'with_greencard' => 'bool',
        'epolis' => 'bool',
        'multiple_trip' => 'bool',
        'gc_plus' => 'bool',
        'price' => 'float',
        'full_price' => 'float',
        'insured_sum' => 'float',
        'gc_plus_price' => 'float',
        'cashback_amount' => 'float',
        'paid' => 'bool',
        'sent_offer' => 'bool',
        'confirm_sms' => 'bool',
        'is_abroad' => 'bool',
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

    public function assist(): ?HasOne
    {
        return $this->hasOne(OrderAssistMeContract::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(OrderFile::class);
    }

    public function tourists(): HasMany
    {
        return $this->hasMany(OrderTourist::class);
    }

    public function vzrDay(): BelongsTo
    {
        return $this->belongsTo(VzrRangeDay::class);
    }
}
