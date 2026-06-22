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

  public function getPreviewUrl(): ?string
  {
    $url = null;

    if ($this->hasMedia('preview')) {
      $url = $this->getFirstMediaUrl('preview');
    } elseif ($this->hasMedia('main')) {
      $url = $this->getFirstMediaUrl('main', 'preview') ?:
        $this->getFirstMediaUrl('main');
    } elseif (method_exists($this, 'product') && $this->product) {
      $url = $this->product->getPreviewUrl();
    }

    // Если ссылка относительная, преобразуем ее в полный URL
    if ($url && !str_starts_with($url, 'http')) {
      return url($url);
    }

    return $url ?: null;
  }

}
