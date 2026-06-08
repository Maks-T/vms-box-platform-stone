<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Support\Enums;

enum PricingMode: string
{
  case MANUAL = 'manual';
  case COMPLEX_DICTIONARY = 'complex_dictionary';
}