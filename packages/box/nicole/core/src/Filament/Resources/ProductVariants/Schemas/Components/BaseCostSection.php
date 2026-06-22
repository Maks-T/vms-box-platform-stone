<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\ProductVariants\Schemas\Components;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Services\PricingManager;
use Nicole\Box\Core\Filament\Resources\ProductVariants\Schemas\ProductVariantForm;

class BaseCostSection
{
  public static function make(): Section
  {
    return Section::make(__('Base Cost (COGS)'))
      ->description(__('Physical purchasing cost and currency for this SKU.'))
      ->schema([
        // Главный тумблер ручного переопределения цен
        Toggle::make('is_manual_pricing')
          ->label(__('Override standard pricing'))
          ->helperText(__('Enable to set custom cost and individual selling prices instead of using the price group.'))
          ->live()
          ->columnSpanFull()
          ->default(false)
          ->visible(function (Get $get, ?Model $record, Component $livewire) {
            $product = ProductVariantForm::resolveProduct($get, $record, $livewire);
            if (!$product) return false;
            $product->loadMissing('type');
            return $product->type?->pricing_mode === 'complex_dictionary';
          }),

        // Поле ввода себестоимости закупки
        TextInput::make('cost_price')
          ->label(__('Cost Price'))
          ->numeric()
          ->default(0)
          ->live(onBlur: true)
          ->required()
          ->disabled(function (Get $get, ?Model $record, Component $livewire) {
            $product = ProductVariantForm::resolveProduct($get, $record, $livewire);
            if (!$product) return false;
            $product->loadMissing('type');
            if ($product->type?->pricing_mode !== 'complex_dictionary') {
              return false;
            }
            return !$get('is_manual_pricing');
          })
          ->afterStateUpdated(function (Get $get, Set $set, $state) {
            $costPrice = (float) $state;
            $costCurrency = $get('currency') ?? 'USD';
            $prices = $get('prices') ?? [];

            foreach ($prices as $key => $priceData) {
              $priceTypeId = $priceData['price_type_id'] ?? null;
              $markup = (float) ($priceData['markup_percent'] ?? 0);

              $inputCurrency = $priceData['input_currency'] ?? null;
              if (!$inputCurrency && $priceTypeId) {
                $priceType = PriceType::with('currency')->find($priceTypeId);
                $inputCurrency = $priceType?->currency?->code ?? 'RUB';
              }
              $inputCurrency ??= 'RUB';

              if ($costPrice > 0) {
                $priceInCostCurrency = $costPrice * (1 + $markup / 100);
                $converted = app(PricingManager::class)->convert($priceInCostCurrency, $costCurrency, $inputCurrency);
                $set("prices.{$key}.desired_price", round($converted, 2));
              }
            }
          })
          ->helperText(function (Get $get, ?Model $record, Component $livewire) {
            $product = ProductVariantForm::resolveProduct($get, $record, $livewire);
            if (!$product) return null;
            $product->loadMissing(['type', 'attributeValues.attribute', 'attributeValues.complexRecord.dictionary']);

            if ($product->type?->pricing_mode === 'complex_dictionary') {
              $pricingManager = app(PricingManager::class);
              $attrId = $product->type->pricing_attribute_id;
              $val = $product->attributeValues->firstWhere('attribute_id', $attrId);

              if ($val && $val->complexRecord) {
                $field = $product->type->pricing_field;
                $meta = $val->complexRecord->meta ?? [];
                $cost = (float) ($meta[$field] ?? 0.0);

                if ($cost > 0) {
                  $schema = $val->complexRecord->dictionary->meta_schema ?? [];
                  $currencyCode = $pricingManager->baseCurrency->code;

                  foreach ($schema as $sField) {
                    if (($sField['key'] ?? '') === $field && isset($sField['currency'])) {
                      $currencyCode = $sField['currency'];
                      break;
                    }
                  }
                  return __('Reference cost from dictionary:') . ' ' . number_format($cost, 2, '.', ' ') . ' ' . $currencyCode;
                }
              }
            }
            return null;
          }),

        // Поле выбора валюты закупки
        Select::make('currency')
          ->label(__('Currency'))
          ->options(fn () => Currency::pluck('code', 'code')->toArray())
          ->default('RUB')
          ->live()
          ->required()
          ->disabled(function (Get $get, ?Model $record, Component $livewire) {
            $product = ProductVariantForm::resolveProduct($get, $record, $livewire);
            if (!$product) return false;
            $product->loadMissing('type');
            if ($product->type?->pricing_mode !== 'complex_dictionary') {
              return false;
            }
            return !$get('is_manual_pricing');
          })
          ->afterStateUpdated(function (Get $get, Set $set, $state) {
            $costPrice = (float) $get('cost_price');
            $costCurrency = $state ?? 'USD';
            $prices = $get('prices') ?? [];

            foreach ($prices as $key => $priceData) {
              $priceTypeId = $priceData['price_type_id'] ?? null;
              $markup = (float) ($priceData['markup_percent'] ?? 0);

              $inputCurrency = $priceData['input_currency'] ?? null;
              if (!$inputCurrency && $priceTypeId) {
                $priceType = PriceType::with('currency')->find($priceTypeId);
                $inputCurrency = $priceType?->currency?->code ?? 'RUB';
              }
              $inputCurrency ??= 'RUB';

              if ($costPrice > 0) {
                $priceInCostCurrency = $costPrice * (1 + $markup / 100);
                $converted = app(PricingManager::class)->convert($priceInCostCurrency, $costCurrency, $inputCurrency);
                $set("prices.{$key}.desired_price", round($converted, 2));
              }
            }
          }),
      ])
      ->columns(2);
  }
}
