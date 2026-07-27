<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Nicole\Box\Core\Http\Resources\Api\V1\Traits\MapsEavAttributes;
use Nicole\Box\Core\Services\PricingManager;

/**
 * Ресурс услуги из матрицы цен.
 *
 * @mixin \Nicole\Box\Core\Models\Product
 */
class ServiceMatrixResource extends JsonResource
{
  use MapsEavAttributes;

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
          $prices[$materialSlug] = (float) $pricingManager->getVariantPrice($variant);
        }
      }
    }

    return [
      /**
       * Внутренний ID услуги.
       * @var int
       * @example 15
       */
      'id' => $this->id,

      /**
       * Системный код услуги для формул и расчетов.
       * @var string
       * @example "cutout_top"
       */
      'code' => $this->code,

      /**
       * Уникальный идентификатор для URL (ЧПУ).
       * @var string
       * @example "cutout-top"
       */
      'slug' => $this->slug,

      /**
       * Внешний код для интеграции с 1C / ERP.
       * @var string|null
       */
      'external_code' => $this->external_code ?? null,

      /**
       * Название услуги.
       * @var string
       * @example "Вырез под накладную мойку"
       */
      'name' => (string)$this->name,

      /**
       * URL картинки превью услуги.
       * @var string|null
       */
      'preview_picture' => $this->getPreviewUrl(),

      /**
       * URL детальной картинки услуги.
       * @var string|null
       */
      'detail_picture' => $this->getDetailUrl(),

      /**
       * Информация о единице измерения.
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

      /**
       * Динамические характеристики (EAV) услуги.
       * @var array<string, array{name: string, type: string, param_type: string|null, is_multiple: bool, value: mixed}>
       */
      'attributes' => $this->mapEavAttributes($this->attributeValues ?? collect()),

      /**
       * Настройки канала услуги.
       * @var object|null
       */
      'settings' => $this->getPublicSettings($this->resource),
    ];
  }

}
