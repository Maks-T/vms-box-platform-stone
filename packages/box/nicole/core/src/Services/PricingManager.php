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

  private function calculateDictionaryPrice(?ProductAttributeValue $val, string $field): ?float
  {
    if (!$val || !$val->complexRecord) {
      return null;
    }

    $meta = $val->complexRecord->meta ?? [];
    $cost = (float) ($meta[$field] ?? 0);

    if ($cost <= 0) {
      return null;
    }

    $markup = (float) ($meta[$field . ComplexDictionary::MARKUP_SUFFIX] ?? 0);
    $schema = $val->complexRecord->dictionary->meta_schema ?? [];

    
    $baseCurrencyCode = $this->baseCurrency->code;
    $currencyCode = $baseCurrencyCode;

    foreach ($schema as $sField) {
      if (($sField['key'] ?? '') === $field) {
        $currencyCode = $sField['currency'] ?? $baseCurrencyCode;
        break;
      }
    }

    $convertedCost = $this->convert($cost, $currencyCode, $baseCurrencyCode);
    return round($convertedCost * (1 + $markup / 100), 2);
  }

  public function getVariantPrice(ProductVariant $variant, ?string $priceTypeSlug = null): float
  {
    
    $priceTypeSlug = $priceTypeSlug ?? $this->defaultPriceType->slug;

    $manualPrice = $variant->getPrice($priceTypeSlug);
    if ($manualPrice > 0) {
      return $manualPrice;
    }

    
    if ($priceTypeSlug === $this->defaultPriceType->slug) {
      $product = $variant->product;
      if (!$product || !$product->type) return 0.0;

      $type = $product->type;
      if ($type->pricing_mode === 'complex_dictionary' && $type->pricing_attribute_id) {
        $attrId = $type->pricing_attribute_id;

        $val = $variant->attributeValues->firstWhere('attribute_id', $attrId)
          ?? $product->attributeValues->firstWhere('attribute_id', $attrId);

        $calculatedPrice = $this->calculateDictionaryPrice($val, (string) $type->pricing_field);

        if ($calculatedPrice !== null) {
          return $calculatedPrice;
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
}
