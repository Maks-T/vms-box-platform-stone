<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Support\Enums;

enum CacheKey: string
{
    case CURRENCIES_LIST = 'core_currencies_list';
    case BASE_CURRENCY = 'core_base_currency';
}
