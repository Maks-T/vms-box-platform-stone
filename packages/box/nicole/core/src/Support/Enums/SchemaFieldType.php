<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Support\Enums;

enum SchemaFieldType: string
{
  case TEXT = 'text';
  case NUMBER = 'number';
  case BOOLEAN = 'boolean';
  case SELECT = 'select';
  case PRICE = 'price';
}
