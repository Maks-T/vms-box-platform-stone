<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB; // Импортируем фасад DB для сверхбыстрой проверки связей
use Nicole\Box\Core\Models\Attribute;
use Nicole\Box\Core\Models\ComplexDictionary;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductAttributeValue;
use Nicole\Box\Core\Models\ProductVariant;
use Nicole\Box\Core\Models\ProductVariantPrice;

class PricingManager
{
  // Локальный кэш в оперативной памяти PHP для исключения N+1 запросов [1.2.1]
  private Collection $priceTypes;
  private array $productTypeAttributes = [];

  public Currency $baseCurrency {
    get => $this->baseCurrency ??= Currency::where('is_default', true)->first()
      ?? throw new \RuntimeException(__('Critical error: Base currency (is_default = true) is not set in the system. Please check currency settings.'));
  }

  public PriceType $defaultPriceType {
    get => $this->defaultPriceType ??= PriceType::where('is_default', true)->first()
      ?? throw new \RuntimeException(__('Critical error: Base price type (is_default = true) is not set in the system.'));
  }

  public Collection $currenciesList {
    get => $this->currenciesList ??= Currency::where('is_active', true)->get();
  }

  public Collection $channelPriceTypes {
    get {
      if (!isset($this->channelPriceTypes)) {
        $channel = config('app.channel', Attribute::CHANNEL_WIDGET);
        $this->channelPriceTypes = PriceType::publicInChannel($channel)->orderBy('sort_order')->get();
      }
      return $this->channelPriceTypes;
    }
  }

  /**
   * Получить тип цены из локального кэша памяти без запросов к БД [1.2.1]
   */
  private function getPriceTypeBySlug(string $slug): PriceType
  {
    if (!isset($this->priceTypes)) {
      $this->priceTypes = PriceType::with('currency')->get()->keyBy('slug');
    }

    return $this->priceTypes->get($slug) ?? $this->defaultPriceType;
  }

  /**
   * Проверить привязку атрибута к типу товара через кэшированную в памяти PHP сводную карту [1.2.1]
   */
  private function isAttributeAttached(int $productTypeId, int $attributeId): bool
  {
    if (!isset($this->productTypeAttributes[$productTypeId])) {
      $this->productTypeAttributes[$productTypeId] = DB::table('attribute_product_type')
        ->where('product_type_id', $productTypeId)
        ->pluck('attribute_id')
        ->toArray();
    }

    return in_array($attributeId, $this->productTypeAttributes[$productTypeId], true);
  }

  public function convert(float $amount, string $fromCode, string $toCode): float
  {
    if ($amount <= 0 || $fromCode === $toCode) {
      return $amount;
    }

    $currencies = $this->currenciesList->keyBy('code');
    $fromRate = $currencies->get($fromCode)?->rate ?? 1.0;
    $toRate = $currencies->get($toCode)?->rate ?? 1.0;

    $baseAmount = $amount * $fromRate;
    return $baseAmount / $toRate;
  }

  public function getVariantPrice(ProductVariant $variant, ?string $priceTypeSlug = null): float
  {
    $priceTypeSlug = $priceTypeSlug ?? $this->defaultPriceType->slug;

    // ОПТИМИЗИРОВАНО: Забираем тип цены из кэша памяти (Минус 222 запроса к БД) [1.1.5]
    $priceType = $this->getPriceTypeBySlug($priceTypeSlug);

    $product = $variant->product;
    $isComplex = $product && $product->type && $product->type->pricing_mode === 'complex_dictionary';

    // 1. Ручные наценки (только если включен ручной режим или товар не использует умный справочник)
    if (!$isComplex || $variant->is_manual_pricing) {

      // ОПТИМИЗИРОВАНО: Если связь цен уже предзагружена, фильтруем её в памяти без SQL-запросов! [1.1.2, 1.2.2]
      if ($variant->relationLoaded('prices')) {
        $priceRecord = $variant->prices->first(function ($price) use ($priceTypeSlug) {
          return $price->type && $price->type->slug === $priceTypeSlug;
        });
      } else {
        // Фолбек, если связь не была предзагружена в контроллере
        $priceRecord = $variant->prices()
          ->whereHas('type', fn($q) => $q->where('slug', $priceTypeSlug))
          ->first();
      }

      if ($priceRecord) {
        $costPrice = (float) $variant->cost_price;
        $costCurrency = $variant->currency;
        $markup = (float) $priceRecord->markup_percent;

        if ($costPrice > 0) {
          $priceInCostCurrency = $costPrice * (1 + $markup / 100);
          $targetCurrency = $priceRecord->type->currency->code ?? $this->baseCurrency->code;

          return round($this->convert($priceInCostCurrency, $costCurrency, $targetCurrency), 2);
        }
      }
    }

    // 2. Расчет по умному справочнику (если ручной режим выключен)
    if ($isComplex && !$variant->is_manual_pricing) {
      $type = $product->type;
      if ($type->pricing_attribute_id) {
        $attrId = $type->pricing_attribute_id;

        // ОПТИМИЗИРОВАНО: Проверяем привязку через кэшированную в памяти PHP сводную карту (Минус 111 запросов к EXISTS!) [1.2.1]
        $isAttached = $this->isAttributeAttached((int) $type->id, (int) $attrId);

        if ($isAttached) {
          $val = $variant->attributeValues->firstWhere('attribute_id', $attrId)
            ?? $product->attributeValues->firstWhere('attribute_id', $attrId);

          $calculatedPrice = $this->calculateDictionaryPrice($val, (string) $type->pricing_field, $priceType);

          if ($calculatedPrice !== null) {
            return $calculatedPrice;
          }
        }
      }
    }

    return 0.0;
  }

  public function getVariantPricesMap(ProductVariant $variant): array
  {
    $prices = [];
    foreach ($this->channelPriceTypes as $type) {
      $prices[$type->slug] = $this->getVariantPrice($variant, $type->slug);
    }

    return $prices;
  }

  public function getRetailPrice(Product $product): float
  {
    return (float) $product->min_price;
  }

  private function calculateDictionaryPrice(?ProductAttributeValue $val, string $field, PriceType $priceType): ?float
  {
    // ИСПРАВЛЕНО: Добавлен жесткий чек empty($val->value_complex_id), блокирующий холостые запросы к БД! [2]
    if (!$val || empty($val->value_complex_id) || !$val->complexRecord) {
      return null;
    }

    $meta = $val->complexRecord->meta ?? [];
    $cost = (float) ($meta[$field] ?? 0);

    if ($cost <= 0) {
      return null;
    }

    // Считываем наценку конкретно под запрашиваемый тип цены
    $markupKey = $field . '_markup_' . $priceType->slug;
    $markup = (float) ($meta[$markupKey] ?? ($meta[$field . ComplexDictionary::MARKUP_SUFFIX] ?? 0));

    $schema = $val->complexRecord->dictionary->meta_schema ?? [];

    $baseCurrencyCode = $this->baseCurrency->code;
    $currencyCode = $baseCurrencyCode;

    // Определяем исходную валюту себестоимости из схемы справочника
    foreach ($schema as $sField) {
      if (($sField['key'] ?? '') === $field) {
        $currencyCode = $sField['currency'] ?? $baseCurrencyCode;
        break;
      }
    }

    $targetCurrencyCode = $priceType->currency->code ?? $baseCurrencyCode;
    $convertedCost = $this->convert($cost, $currencyCode, $targetCurrencyCode);

    return round($convertedCost * (1 + $markup / 100), 2);
  }

  /**
   * Расчетная базовая себестоимость вариации с учетом справочников.
   */
  public function getVariantCostPrice(ProductVariant $variant): float
  {
    if ($variant->is_manual_pricing) {
      return (float) $variant->cost_price;
    }

    $product = $variant->product;
    if ($product && $product->type && $product->type->pricing_mode === 'complex_dictionary') {
      $attrId = $product->type->pricing_attribute_id;
      $field = $product->type->pricing_field;

      $val = $variant->attributeValues->firstWhere('attribute_id', $attrId)
        ?? $product->attributeValues->firstWhere('attribute_id', $attrId);

      // ИСПРАВЛЕНО: Добавлен жесткий чек empty($val->value_complex_id)
      if ($val && !empty($val->value_complex_id) && $val->complexRecord) {
        $meta = $val->complexRecord->meta ?? [];
        return (float) ($meta[$field] ?? 0.0);
      }
    }

    return (float) $variant->cost_price;
  }

  /**
   * Валюта базовой себестоимости вариации с учетом справочников.
   */
  public function getVariantCostCurrency(ProductVariant $variant): string
  {
    if ($variant->is_manual_pricing) {
      return $variant->currency;
    }

    $product = $variant->product;
    if ($product && $product->type && $product->type->pricing_mode === 'complex_dictionary') {
      $attrId = $product->type->pricing_attribute_id;
      $field = $product->type->pricing_field;

      $val = $variant->attributeValues->firstWhere('attribute_id', $attrId)
        ?? $product->attributeValues->firstWhere('attribute_id', $attrId);

      // ИСПРАВЛЕНО: Добавлен жесткий чек empty($val->value_complex_id)
      if ($val && !empty($val->value_complex_id) && $val->complexRecord) {
        $schema = $val->complexRecord->dictionary->meta_schema ?? [];
        foreach ($schema as $sField) {
          if (($sField['key'] ?? '') === $field && isset($sField['currency'])) {
            return $sField['currency'];
          }
        }
      }
    }

    return $variant->currency;
  }
}
