<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Support\Enums;

enum TaxCategory: string
{
    case HARDWARE = 'hardware';
    case LABOR = 'labor';
    case SOFTWARE = 'software';
    case SHIPPING = 'shipping';
    case NONE = 'none';

    public function label(): string
    {
        return match ($this) {
            self::HARDWARE => __('Hardware (Equipment)'),
            self::LABOR => __('Labor (Services)'),
            self::SOFTWARE => __('Software (Licenses)'),
            self::SHIPPING => __('Shipping'),
            self::NONE => __('No Tax (Exempt)'),
        };
    }
}
