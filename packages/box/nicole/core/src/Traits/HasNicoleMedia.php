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
   * Получить URL превью-изображения (с автоматическим фолбеком).
   */
  public function getPreviewUrl(): ?string
  {
    $url = null;

    if ($this->hasMedia('preview')) {
      $url = $this->getFirstMediaUrl('preview');
    } elseif ($this->hasMedia('main')) {
      $url = $this->getFirstMediaUrl('main', 'preview') ?:
        $this->getFirstMediaUrl('main');
    } elseif (method_exists($this, 'product') && $this->product) {
      return $this->product->getPreviewUrl();
    }

    if (empty($url) && method_exists($this, 'variants')) {
      /** @var \Nicole\Box\Core\Models\ProductVariant|null $defaultVariant */
      $defaultVariant = $this->variants()
        ->where('is_active', true)
        ->orderByDesc('is_default')
        ->first();

      if ($defaultVariant) {
        return $defaultVariant->getPreviewUrl();
      }
    }

    if (empty($url)) {
      return null;
    }

    return rtrim(config('app.url'), '/') . parse_url($url, PHP_URL_PATH);
  }

  /**
   * Получить URL детального изображения (с автоматическим фолбеком).
   */
  public function getDetailUrl(): ?string
  {
    $url = null;

    if ($this->hasMedia('main')) {
      $url = $this->getFirstMediaUrl('main');
    } elseif (method_exists($this, 'product') && $this->product) {
      return $this->product->getDetailUrl();
    }

    if (empty($url) && method_exists($this, 'variants')) {
      /** @var \Nicole\Box\Core\Models\ProductVariant|null $defaultVariant */
      $defaultVariant = $this->variants()
        ->where('is_active', true)
        ->orderByDesc('is_default')
        ->first();

      if ($defaultVariant) {
        return $defaultVariant->getDetailUrl();
      }
    }

    if (empty($url)) {
      return null;
    }

    return rtrim(config('app.url'), '/') . parse_url($url, PHP_URL_PATH);
  }

}
