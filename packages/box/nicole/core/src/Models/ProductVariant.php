<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Nicole\Box\Core\Traits\HasExternalCode;
use Nicole\Box\Core\Traits\HasNicoleMedia;
use Nicole\Box\Core\Traits\HasSettings;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute as EloquentAttribute;

class ProductVariant extends Model implements HasMedia
{
  use HasExternalCode;
  use HasNicoleMedia;
  use HasSettings;
  use HasTranslations;
  use HasFactory;

  protected $fillable = [
    'external_code',
    'product_id',
    'sku',
    'cost_price',
    'currency',
    'stock',
    'is_default',
    'is_active',
    'sort_order',
  ];

  protected function casts(): array
  {
    return [
      'cost_price' => 'float',
      'stock' => 'float',
      'is_default' => 'boolean',
      'is_active' => 'boolean',
      'sort_order' => 'integer',
    ];
  }

  public function product(): BelongsTo
  {
    return $this->belongsTo(Product::class);
  }

  public function attributeValues(): MorphMany
  {
    return $this->morphMany(ProductAttributeValue::class, 'attributable');
  }

  public function stocks(): HasMany
  {
    return $this->hasMany(Stock::class, 'product_variant_id');
  }

  public function prices(): HasMany
  {
    return $this->hasMany(ProductVariantPrice::class);
  }

  public function getPrice(?string $typeSlug = null): float
  {

    $typeSlug = $typeSlug ?? app(\Nicole\Box\Core\Services\PricingManager::class)->defaultPriceType->slug;

    return (float)$this->prices()
      ->whereHas('type', fn($q) => $q->where('slug', $typeSlug))
      ->value('price') ?? 0.0;
  }

  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('main')->singleFile();
    $this->addMediaCollection('preview')->singleFile();
  }

  protected static function booted(): void
  {
    // Пересчет цен базового товара
    $callback = function (ProductVariant $variant) {
      $variant->product?->refreshMinPrice();
    };

    static::saved($callback);
    static::deleted($callback);

    // Не даем снять единственную галочку дефолта у товара
    static::saving(function (ProductVariant $variant) {
      if (!$variant->is_default) {
        $otherDefaultExists = ProductVariant::where('product_id', $variant->product_id)
          ->where('id', '!=', $variant->id)
          ->where('is_default', true)
          ->exists();

        if (!$otherDefaultExists) {
          $variant->is_default = true;
        }
      }
    });

    // Снимаем дефолт у соседних модификаций этого же товара
    static::saved(function (ProductVariant $variant) {
      if ($variant->is_default) {
        ProductVariant::where('product_id', $variant->product_id)
          ->where('id', '!=', $variant->id)
          ->update(['is_default' => false]);
      }
    });

    // Запрещаем удаление единственного дефолтного варианта
    static::deleting(function (ProductVariant $variant) {
      if ($variant->is_default) {
        $hasOtherVariants = ProductVariant::where('product_id', $variant->product_id)
          ->where('id', '!=', $variant->id)
          ->exists();

        if ($hasOtherVariants) {
          throw new \Exception(
            __('Cannot delete the default variant. Please set another variant as default first.')
          );
        }
      }
    });

  }

  /**
   * Виртуальное свойство: Итоговая розничная цена (Справочник ИЛИ Ручная)
   * Использование: $variant->retail_price
   */
  protected function retailPrice(): EloquentAttribute
  {
    return EloquentAttribute::make(
      get: fn () => app(\Nicole\Box\Core\Services\PricingManager::class)->getVariantPrice($this),
    );
  }

  protected static function newFactory(): \Nicole\Box\Core\Database\Factories\ProductVariantFactory
  {
    return \Nicole\Box\Core\Database\Factories\ProductVariantFactory::new();
  }

}
