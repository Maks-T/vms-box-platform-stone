<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Support\Enums;

enum FilterUiType: string
{
  case CHECKBOX = 'checkbox';
  case SELECT = 'select';
  case COLOR = 'color';
  case RANGE = 'range';
}