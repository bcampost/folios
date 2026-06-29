<?php

namespace App\Enums;

enum BranchEnum: string
{
    case AGS  = 'AGS';
    case CDMX = 'CDMX';
    case QRO  = 'QRO';
    case MTY  = 'MTY';
    case CAPACITACION  = 'CAPACITACION';
    case LEON  = 'LEON';
    case SLP  = 'SLP';

    public static function getId(string $name) : int
    {
        return match ($name) {
            self::AGS->value => 1,
            self::CDMX->value => 2,
            self::QRO->value => 3,
            self::MTY->value => 4,
            self::CAPACITACION->value => 5,
            self::LEON->value => 6,
            self::SLP->value => 7,
        };
    }
}
