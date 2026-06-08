<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Nicole\Box\Core\Support\Enums\CacheKey;
use Nicole\Box\Core\Traits\HasExternalCode;
use Nicole\Box\Core\Traits\HasGlobalDefault;
use Nicole\Box\Core\Traits\HasSettings;
use Spatie\Translatable\HasTranslations;

class Currency extends Model
{
  use HasExternalCode;
  use HasSettings;
  use HasTranslations;
  use HasGlobalDefault;

  protected $fillable = [
    'external_code',
    'code',
    'name',
    'symbol',
    'rate',
    'is_default',
    'is_active',
    'sort_order',
  ];

  public array $translatable = ['name'];

  protected function casts(): array
  {
    return [
      'rate' => 'float',
      'is_default' => 'boolean',
      'is_active' => 'boolean',
    ];
  }

  protected static function booted(): void
  {
    static::saved(function (Currency $currency) {
      Cache::forget(CacheKey::CURRENCIES_LIST->value);
      Cache::forget(CacheKey::BASE_CURRENCY->value);

      // Оставляем здесь только специфичную логику нормализации курса валюты
      if ($currency->is_default && (float)$currency->rate !== 1.0) {
        $currency->updateQuietly(['rate' => 1.0]);
      }
    });
  }

}
