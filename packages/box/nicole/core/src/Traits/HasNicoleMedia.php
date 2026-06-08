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
        if ($this->hasMedia('preview')) {
            return $this->getFirstMediaUrl('preview');
        }

        if ($this->hasMedia('main')) {
            return $this->getFirstMediaUrl('main', 'preview') ?:
              $this->getFirstMediaUrl('main');
        }

        if (method_exists($this, 'product') && $this->product) {
            return $this->product->getPreviewUrl();
        }

        return null;
    }
}
