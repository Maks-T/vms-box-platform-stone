<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Services;

use Illuminate\Support\Collection;
use Nicole\Box\Core\Models\Attribute;
use Nicole\Box\Core\Models\ComplexDictionary;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductAttributeValue;
use Nicole\Box\Core\Models\ProductVariant;

class PricingManager
{
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

    $priceType = PriceType::with('currency')->where('slug', $priceTypeSlug)->first() ?? $this->defaultPriceType;

    $product = $variant->product;
    $isComplex = $product && $product->type && $product->type->pricing_mode === 'complex_dictionary';

    // 1. Ручные наценки (только если включен ручной режим или товар не использует умный справочник)
    if (!$isComplex || $variant->is_manual_pricing) {
      $priceRecord = $variant->prices()
        ->whereHas('type', fn($q) => $q->where('slug', $priceTypeSlug))
        ->first();

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

        // Проверяем привязку атрибута к типу товара во избежание фантомных расчетов после удаления из связи
        $isAttached = $type->attributes()->where('attributes.id', $attrId)->exists();

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
    if (!$val || !$val->complexRecord) {
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

      if ($val && $val->complexRecord) {
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

      if ($val && $val->complexRecord) {
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
