<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\TaggableStore;

class CatalogCache
{
  /**
   * Умное кэширование каталога.
   * Автоматически использует теги на Redis/Memcached, либо Cache Busting версию на File/Database.
   */
  public static function remember(string $key, int $ttl, \Closure $callback)
  {
    $store = Cache::getStore();

    if ($store instanceof TaggableStore) {
      return Cache::tags(['catalog'])->remember($key, $ttl, $callback);
    }

    // Если теги не поддерживаются (File/Database)
    $version = Cache::get('catalog_version', 1);
    $versionedKey = "v{$version}_{$key}";

    return Cache::remember($versionedKey, $ttl, $callback);
  }

  /**
   * Сброс (инвалидация) всего кэша каталога.
   */
  public static function invalidate(): void
  {
    $store = Cache::getStore();

    if ($store instanceof TaggableStore) {
      Cache::tags(['catalog'])->flush();
    } else {
      Cache::increment('catalog_version');
    }
  }

}
