<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\ProductVariants\Schemas\Components;

use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\PriceGroup;
use Nicole\Box\Core\Services\PricingManager;
use Nicole\Box\Core\Filament\Resources\ProductVariants\Schemas\ProductVariantForm;

class BaseCostSection
{
  public static function make(): Section
  {
    return Section::make(__('Base Cost (COGS)'))
      ->description(__('Physical purchasing cost and currency for this SKU.'))
      ->schema([
        Toggle::make('is_manual_pricing')
          ->label(__('Override standard pricing'))
          ->helperText(__('Enable to set custom cost and individual selling prices instead of using the price group.'))
          ->live()
          ->columnSpanFull()
          ->default(false),

        Select::make('price_group_id')
          ->label(__('Price Group'))
          ->relationship('priceGroup', 'name', function ($query, Get $get, ?Model $record, Component $livewire) {
            $product = ProductVariantForm::resolveProduct($get, $record, $livewire);
            if ($product && $product->type) {
              return $query->where('product_family_id', $product->type->family_id);
            }
            return $query;
          })
          ->searchable()
          ->preload()
          ->live()
          ->required(fn(Get $get) => !$get('is_manual_pricing'))
          ->hidden(fn(Get $get) => (bool)$get('is_manual_pricing'))
          ->columnSpanFull(),

        TextEntry::make('price_group_reference')
          ->hiddenLabel()
          ->visible(fn(Get $get) => !(bool)$get('is_manual_pricing') && filled($get('price_group_id')))
          ->columnSpanFull()
          ->state(function (Get $get, ?Model $record, Component $livewire) {
            $priceGroupId = $get('price_group_id');
            if (!$priceGroupId) return null;

            $priceGroup = PriceGroup::find($priceGroupId);
            if (!$priceGroup) return null;

            $pricingManager = app(PricingManager::class);
            $meta = $priceGroup->meta ?? [];
            $cost = (float)($meta['purchase_cost'] ?? 0);
            $purchaseCurrency = $meta['purchase_currency'] ?? 'USD';

            if ($cost <= 0) {
              return __('Base cost is not set in the selected price group.'); // Локализация через __() [1]
            }

            $html = "<div style='margin-top: 10px; padding: 15px; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; border-left: 4px solid #b8945a;'>";
            $html .= "<div style='font-size: 0.8rem; text-transform: uppercase; color: #b8945a; font-weight: 700; margin-bottom: 8px;'>" . __('Price Group Reference Information') . ": " . htmlspecialchars($priceGroup->getTranslation('name', app()->getLocale())) . "</div>";
            $html .= "<div style='font-size: 0.9rem; margin-bottom: 12px; color: #374151;'>" . __('Base Cost') . ": <strong>" . number_format($cost, 2, '.', ' ') . " " . $purchaseCurrency . "</strong></div>";

            $html .= "<table style='width: 100%; border-collapse: collapse; font-size: 0.85rem;'>";
            $html .= "<thead><tr style='border-bottom: 1px solid #e5e7eb; color: #6b7280;'><th style='text-align: left; padding: 4px 0;'>" . __('Price Type') . "</th><th style='text-align: center;'>" . __('Markup') . "</th><th style='text-align: right;'>" . __('Final Price') . "</th></tr></thead>";
            $html .= "<tbody>";

            foreach ($pricingManager->channelPriceTypes as $type) {
              $markup = (float)($meta["markup_{$type->slug}"] ?? 0);

              $targetCurrency = $type->currency->code ?? 'RUB';
              $convertedCost = $pricingManager->convert($cost, $purchaseCurrency, $targetCurrency);
              $finalPrice = $convertedCost * (1 + $markup / 100);

              $symbol = $type->currency->symbol ?? '₽';
              $formattedPrice = number_format($finalPrice, 2, '.', ' ') . ' ' . $symbol;

              $html .= "<tr style='border-bottom: 1px solid #f3f4f6;'>";
              $html .= "<td style='padding: 6px 0; color: #4b5563;'>" . htmlspecialchars($type->getTranslation('name', app()->getLocale())) . "</td>";
              $html .= "<td style='padding: 6px 0; text-align: center; color: #4b5563;'>" . $markup . "%</td>";
              $html .= "<td style='padding: 6px 0; text-align: right; font-weight: 700; color: #111827;'>" . $formattedPrice . "</td>";
              $html .= "</tr>";
            }

            $html .= "</tbody></table></div>";

            return new HtmlString($html);
          }),

        TextInput::make('cost_price')
          ->label(__('Cost Price'))
          ->numeric()
          ->default(0)
          ->live(onBlur: true)
          ->required()
          ->visible(fn(Get $get) => (bool)$get('is_manual_pricing'))
          ->afterStateUpdated(function (Get $get, Set $set, $state) {
            $costPrice = (float)$state;
            $costCurrency = $get('currency') ?? 'USD';
            $prices = $get('prices') ?? [];

            foreach ($prices as $key => $priceData) {
              $priceTypeId = $priceData['price_type_id'] ?? null;
              $markup = (float)($priceData['markup_percent'] ?? 0);

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

        Select::make('currency')
          ->label(__('Currency'))
          ->options(fn() => Currency::pluck('code', 'code')->toArray())
          ->default('RUB')
          ->live()
          ->required()
          ->visible(fn(Get $get) => (bool)$get('is_manual_pricing'))
          ->afterStateUpdated(function (Get $get, Set $set, $state) {
            $costPrice = (float)$get('cost_price');
            $costCurrency = $state ?? 'USD';
            $prices = $get('prices') ?? [];

            foreach ($prices as $key => $priceData) {
              $priceTypeId = $priceData['price_type_id'] ?? null;
              $markup = (float)($priceData['markup_percent'] ?? 0);

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
