<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Traits;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasNicoleMedia
{
  use InteractsWithMedia;

  public function registerMediaConversions(?Media $media = null): void
  {
    if ($media && $media->getCustomProperty('skip_conversions')) {
      return;
    }

    $this->addMediaConversion('preview')
      ->fit(Fit::Max, 400, 400)
      ->format('webp')
      ->quality(80)
      ->sharpen(10)
      ->nonQueued()
      ->performOnCollections('main');
  }

  /**
   * Получить URL превью-изображения.
   *
   * Ищет собственную картинку. Если её нет и это базовый товар,
   * заимствует изображение у дефолтного активного варианта из памяти.
   */
  public function getPreviewUrl(): ?string
  {
    $url = null;

    // Сначала проверяем собственные медиафайлы текущей модели (будь то продукт или вариант)
    if ($this->hasMedia('preview')) {
      $url = $this->getFirstMediaUrl('preview');
    } elseif ($this->hasMedia('main')) {
      $url = $this->getFirstMediaUrl('main', 'preview') ?:
        $this->getFirstMediaUrl('main');
    }

    // Если картинки нет, и это Базовый товар - берем превью у дефолтного варианта из памяти
    if (empty($url) && $this->relationLoaded('variants')) {
      /** @var \Nicole\Box\Core\Models\ProductVariant|null $defaultVariant */
      $defaultVariant = $this->variants
        ->where('is_active', true)
        ->sortByDesc('is_default') // Фильтруем коллекцию в памяти PHP без запросов к БД
        ->first();

      if ($defaultVariant) {
        return $defaultVariant->getPreviewUrl(); // Вызываем получение у варианта напрямую
      }
    }

    if (empty($url)) {
      return null;
    }

    return rtrim(config('app.url'), '/') . parse_url($url, PHP_URL_PATH);
  }

  /**
   * Получить URL детального изображения.
   *
   * Ищет собственную картинку. Если её нет и это базовый товар,
   * заимствует изображение у дефолтного активного варианта из памяти.
   */
  public function getDetailUrl(): ?string
  {
    $url = null;

    // Проверяем собственные медиафайлы текущей модели
    if ($this->hasMedia('main')) {
      $url = $this->getFirstMediaUrl('main');
    }

    // Если картинки нет, и это Базовый товар - берем фото у дефолтного варианта из памяти
    if (empty($url) && $this->relationLoaded('variants')) {
      /** @var \Nicole\Box\Core\Models\ProductVariant|null $defaultVariant */
      $defaultVariant = $this->variants
        ->where('is_active', true)
        ->sortByDesc('is_default') // Фильтруем коллекцию в памяти PHP без запросов к БД
        ->first();

      if ($defaultVariant) {
        return $defaultVariant->getDetailUrl(); // Вызываем получение у варианта напрямую
      }
    }

    if (empty($url)) {
      return null;
    }

    return rtrim(config('app.url'), '/') . parse_url($url, PHP_URL_PATH);
  }

}
