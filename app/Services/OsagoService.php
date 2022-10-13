<?php

namespace App\Services;

class OsagoService
{
    const TARIFF_ECONOM = 'econom';
    const TARIFF_STANDART = 'standart';
    const TARIFF_MAXIMUM = 'maximum';
    const TARIFFS = [
        self::TARIFF_ECONOM,
        self::TARIFF_STANDART,
        self::TARIFF_MAXIMUM,
    ];
}