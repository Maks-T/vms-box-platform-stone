<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Nicole\Box\Core\Http\Resources\Api\V1\Traits\HasSharedResourceFields;
use Nicole\Box\Core\Services\PricingManager;

/**
 * @mixin \Nicole\Box\Core\Models\Product
 */
class ServiceMatrixResource extends JsonResource
{
  use HasSharedResourceFields;

  public function toArray(Request $request): array
  {
    $pricingManager = app(PricingManager::class);
    $defaultPriceId = $pricingManager->defaultPriceType->id;

    $prices = [];

    foreach ($this->variants as $variant) {
      $materialSlug = $variant->attributeValues->firstWhere('attribute.code', 'target_material')?->option?->slug;
      if ($materialSlug) {
        $priceRecord = $variant->prices->firstWhere('price_type_id', $defaultPriceId);
        if ($priceRecord) {
          $prices[$materialSlug] = (float) $priceRecord->price;
        }
      }
    }

    return array_merge($this->getSharedEntityFields($this->resource), [
      /**
       * Информация о единице измерения
       * @var array{slug: string, name: string, symbol: string}|null
       */
      'unit' => $this->unit ? [
        'slug' => $this->unit->slug,
        'name' => (string)$this->unit->name,
        'symbol' => (string)$this->unit->symbol,
      ] : null,

      /**
       * Ассоциативный массив цен в разрезе типов материалов.
       * @var array<string, float>
       * @example {"acrylic_stone": 1650.0, "quartz_stone": 2500.0}
       */
      'prices' => $prices,
    ]);
  }
}
