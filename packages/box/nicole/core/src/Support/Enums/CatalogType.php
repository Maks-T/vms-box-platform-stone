<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Support\Enums;

enum CatalogType: string
{
  case PRODUCT = 'product';
  case SERVICE = 'service';
  case BUNDLE = 'bundle';

  public function label(): string
  {
    return match ($this) {
      self::PRODUCT => __('Product (Physical)'),
      self::SERVICE => __('Service / Work'),
      self::BUNDLE => __('Bundle (Kit)'),
    };
  }
}