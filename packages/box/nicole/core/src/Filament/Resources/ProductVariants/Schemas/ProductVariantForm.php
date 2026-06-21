<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\ProductVariants\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Nicole\Box\Core\Filament\Concerns\HasDynamicEavFields;
use Nicole\Box\Core\Filament\Forms\Tabs\MediaGalleryTab;
use Nicole\Box\Core\Filament\Forms\Tabs\SalesChannelsTab;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Services\PricingManager;

class ProductVariantForm
{
  use HasDynamicEavFields;

  public static function configure(Schema $schema): Schema
  {
    return $schema->components([
      Tabs::make('VariantData')
        ->tabs([
          Tabs\Tab::make(__('Identity & Status'))
            ->icon('heroicon-o-tag')
            ->schema([
              Grid::make(3)->schema([
                Section::make(__('Variant Identity'))
                  ->columnSpan(2)
                  ->schema([
                    Select::make('product_id')
                      ->label(__('Parent Product'))
                      ->relationship('product', 'name')
                      ->required()
                      ->searchable()
                      ->preload()
                      ->live()
                      ->disabled(fn (string $context) => $context === 'edit')
                      ->hidden(
                        fn (Component $livewire) => $livewire instanceof RelationManager,
                      ),

                    TextInput::make('sku')
                      ->label(__('SKU / Article'))
                      ->required()
                      ->unique(ignoreRecord: true)
                      ->maxLength(255),

                    TextInput::make('external_code')
                      ->label(__('External Code'))
                      ->nullable()
                      ->helperText(__('Used for API / 1C integrations')),
                  ])
                  ->columns(2),

                Section::make(__('Status'))
                  ->columnSpan(1)
                  ->schema([
                    Toggle::make('is_default')
                      ->label(__('Default Variant'))
                      ->helperText(__('Selected by default in the catalog')),

                    Toggle::make('is_active')
                      ->label(__('Is Active'))
                      ->default(true),
                  ]),
              ]),
            ]),

          Tabs\Tab::make(__('Technical Specifications'))
            ->icon('heroicon-o-adjustments-vertical')
            ->schema(function (Get $get, ?Model $record) {
              $productId = $get('product_id') ?? $record?->product_id;
              if (! $productId) {
                return [];
              }
              $productType = Product::find($productId)?->product_type_id;


              return static::getDynamicEavSchema($productType, 'product_variant');
            })
            ->columns(3),

          Tabs\Tab::make(__('Pricing & Economy'))
            ->icon('heroicon-o-banknotes')
            ->schema([

              // Секция базовой себестоимости (Закупка)
              Section::make(__('Base Cost (COGS)'))
                ->description(
                  __('Physical purchasing cost and currency for this SKU.'),
                )
                ->schema([

                  // 1. Главный тумблер (привязан напрямую к колонке БД)
                  Toggle::make('is_manual_pricing')
                    ->label(__('Override standard pricing'))
                    ->helperText(__('Enable to set custom cost and individual selling prices instead of using the price group.'))
                    ->live()
                    ->columnSpanFull()
                    ->default(false)
                    ->visible(function (Get $get, ?Model $record) {
                      $productId = $get('product_id') ?? $record?->product_id;
                      if (!$productId) return false;
                      $product = Product::with('type')->find($productId);
                      return $product?->type?->pricing_mode === 'complex_dictionary';
                    }),

                  // 2. Себестоимость
                  TextInput::make('cost_price')
                    ->label(__('Cost Price'))
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: true)
                    ->required()
                    ->disabled(function (Get $get, ?Model $record) {
                      $productId = $get('product_id') ?? $record?->product_id;
                      if (!$productId) return false;
                      $product = Product::with('type')->find($productId);
                      if ($product?->type?->pricing_mode !== 'complex_dictionary') {
                        return false;
                      }
                      return !$get('is_manual_pricing');
                    })
                    // Принудительный пересчет цен продажи при изменении себестоимости закупки
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                      $costPrice = (float) $state;
                      $costCurrency = $get('currency') ?? 'USD';
                      $prices = $get('prices') ?? [];

                      foreach ($prices as $key => $priceData) {
                        $priceTypeId = $priceData['price_type_id'] ?? null;
                        $markup = (float) ($priceData['markup_percent'] ?? 0);
                        $inputCurrencyType = $priceData['input_currency_type'] ?? 'sales';

                        if ($costPrice > 0 && $priceTypeId) {
                          $priceType = PriceType::with('currency')->find($priceTypeId);
                          $targetCurrency = $priceType?->currency?->code ?? 'RUB';

                          $priceInCostCurrency = $costPrice * (1 + $markup / 100);

                          if ($inputCurrencyType === 'cost') {
                            $set("prices.{$key}.desired_price", round($priceInCostCurrency, 2));
                          } else {
                            $converted = app(PricingManager::class)->convert($priceInCostCurrency, $costCurrency, $targetCurrency);
                            $set("prices.{$key}.desired_price", round($converted, 2));
                          }
                        }
                      }
                    }),

                  // 3. Валюта закупки
                  Select::make('currency')
                    ->label(__('Currency'))
                    ->options(
                      fn () => Currency::pluck('code', 'code')->toArray(),
                    )
                    ->default('RUB')
                    ->live()
                    ->required()
                    ->disabled(function (Get $get, ?Model $record) {
                      $productId = $get('product_id') ?? $record?->product_id;
                      if (!$productId) return false;
                      $product = Product::with('type')->find($productId);
                      if ($product?->type?->pricing_mode !== 'complex_dictionary') {
                        return false;
                      }
                      return !$get('is_manual_pricing');
                    }),
                ])
                ->columns(2),

              // 4. Матрица ручных цен продаж
              Repeater::make('prices')
                ->label(__('Sales Pricing Matrix'))
                ->relationship('prices')
                ->visible(function (Get $get, ?Model $record) {
                  $productId = $get('product_id') ?? $record?->product_id;
                  if (!$productId) return true;
                  $product = Product::with('type')->find($productId);
                  $isComplex = $product?->type?->pricing_mode === 'complex_dictionary';
                  return !$isComplex || $get('is_manual_pricing');
                })
                ->schema([
                  Select::make('price_type_id')
                    ->label(__('Price Type'))
                    ->relationship('type', 'name')
                    ->required()
                    ->distinct()
                    ->live()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                  Grid::make(3)
                    ->schema([

                      // Валюта ввода
                      Select::make('input_currency_type')
                        ->label(__('Input Currency'))
                        ->options(function (Get $get) {
                          $costCurrency = $get('../../currency') ?? 'USD';
                          $priceType = PriceType::with('currency')->find($get('price_type_id'));
                          $targetCurrency = $priceType?->currency?->code ?? 'RUB';

                          if ($costCurrency === $targetCurrency) {
                            return [$costCurrency => $costCurrency];
                          }

                          return [
                            'cost' => $costCurrency . ' (' . __('in purchase currency') . ')',
                            'sales' => $targetCurrency . ' (' . __('in selling currency') . ')',
                          ];
                        })
                        ->default('sales')
                        ->live()
                        ->dehydrated(false)
                        ->disabled(fn (Get $get) => (float) $get('../../cost_price') <= 0)
                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                          $costPrice = (float) $get('../../cost_price');
                          $costCurrency = $get('../../currency') ?? 'USD';
                          $priceTypeId = $get('price_type_id');
                          $markup = (float) $get('markup_percent');

                          if ($costPrice > 0 && $priceTypeId) {
                            $priceType = PriceType::with('currency')->find($priceTypeId);
                            $targetCurrency = $priceType?->currency?->code ?? 'RUB';

                            $priceInCostCurrency = $costPrice * (1 + $markup / 100);

                            if ($state === 'cost') {
                              $set('desired_price', round($priceInCostCurrency, 2));
                            } else {
                              $converted = app(PricingManager::class)->convert($priceInCostCurrency, $costCurrency, $targetCurrency);
                              $set('desired_price', round($converted, 2));
                            }
                          }
                        }),

                      // Наценка (%)
                      TextInput::make('markup_percent')
                        ->label(__('Markup (%)'))
                        ->numeric()
                        ->suffix('%')
                        ->required()
                        ->live(onBlur: true)
                        ->disabled(fn (Get $get) => (float) $get('../../cost_price') <= 0)
                        ->placeholder(fn (Get $get) => (float) $get('../../cost_price') <= 0 ? __('Locked') : '0')
                        ->helperText(fn (Get $get) => (float) $get('../../cost_price') <= 0 ? __('Specify cost price first') : null)
                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                          $costPrice = (float) $get('../../cost_price');
                          $costCurrency = $get('../../currency') ?? 'USD';
                          $priceTypeId = $get('price_type_id');
                          $markup = (float) $state;

                          if ($costPrice > 0 && $priceTypeId) {
                            $priceType = PriceType::with('currency')->find($priceTypeId);
                            $targetCurrency = $priceType?->currency?->code ?? 'RUB';
                            $inputCurrencyType = $get('input_currency_type');

                            $priceInCostCurrency = $costPrice * (1 + $markup / 100);

                            if ($inputCurrencyType === 'cost') {
                              $set('desired_price', round($priceInCostCurrency, 2));
                            } else {
                              $converted = app(PricingManager::class)->convert($priceInCostCurrency, $costCurrency, $targetCurrency);
                              $set('desired_price', round($converted, 2));
                            }
                          }
                        }),

                      // Итоговая цена
                      TextInput::make('desired_price')
                        ->label(__('Final Price'))
                        ->numeric()
                        ->live(onBlur: true)
                        ->disabled(fn (Get $get) => (float) $get('../../cost_price') <= 0)
                        ->placeholder(fn (Get $get) => (float) $get('../../cost_price') <= 0 ? __('Locked') : '0')
                        ->dehydrated(false)
                        ->afterStateHydrated(function (TextInput $component, Get $get, ?Model $record) {
                          if ($record && (float)$get('markup_percent') > 0) {
                            // ИСПРАВЛЕНО: Безопасно получаем родительскую модель ProductVariant вместо ProductVariantPrice
                            /** @var \Nicole\Box\Core\Models\ProductVariant|null $variant */
                            $variant = $record instanceof \Nicole\Box\Core\Models\ProductVariantPrice ? $record->variant : null;

                            if ($variant) {
                              $priceType = PriceType::find($get('price_type_id'));
                              $priceTypeSlug = $priceType?->slug ?? 'retail';
                              $calculated = app(PricingManager::class)->getVariantPrice($variant, $priceTypeSlug);
                              $component->state($calculated);
                            }
                          }
                        })
                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                          $costPrice = (float) $get('../../cost_price');
                          $costCurrency = $get('../../currency') ?? 'USD';
                          $priceTypeId = $get('price_type_id');
                          $desiredPrice = (float) $state;

                          // Если поле очистили, наценка автоматически сбрасывается в 0.0
                          if ($desiredPrice <= 0) {
                            $set('markup_percent', 0.0000000000);
                            $set('desired_price', $costPrice);
                            return;
                          }

                          if ($costPrice <= 0 || !$priceTypeId) {
                            return;
                          }

                          $priceType = PriceType::with('currency')->find($priceTypeId);
                          $targetCurrency = $priceType?->currency?->code ?? 'RUB';
                          $inputCurrencyType = $get('input_currency_type');

                          if ($inputCurrencyType === 'cost') {
                            $markup = (($desiredPrice / $costPrice) - 1) * 100;
                          } else {
                            $priceInCostCurrency = app(PricingManager::class)->convert($desiredPrice, $targetCurrency, $costCurrency);
                            $markup = (($priceInCostCurrency / $costPrice) - 1) * 100;
                          }

                          $set('markup_percent', round($markup, 10));
                        }),
                    ]),
                ]),

              // 5. Текстовый блок справочной цены
              TextEntry::make('calculated_dictionary_info')
                ->hiddenLabel()
                ->columnSpanFull()
                ->visible(function (Get $get, ?Model $record) {
                  $productId = $get('product_id') ?? $record?->product_id;
                  if (!$productId) return false;
                  $product = Product::with('type')->find($productId);
                  $isComplex = $product?->type?->pricing_mode === 'complex_dictionary';
                  return $isComplex && !$get('is_manual_pricing');
                })
                ->state(function (Get $get, ?Model $record) {
                  if (!$record) return '—';

                  $pricingManager = app(PricingManager::class);
                  $lines = [];

                  foreach ($pricingManager->channelPriceTypes as $type) {
                    $originalMarkup = $record->markup_percent;
                    $record->markup_percent = 0.0;

                    $calculatedPrice = $pricingManager->getVariantPrice($record, $type->slug);
                    $record->markup_percent = $originalMarkup;

                    $symbol = $type->currency?->symbol ?? '₽';
                    $lines[] = (string) $type->name . ': ' . number_format($calculatedPrice, 2, '.', ' ') . ' ' . $symbol;
                  }

                  return __('Pricing is managed by dictionary:') . "\n" . implode("\n", $lines);
                }),
            ]),

          Tabs\Tab::make(__('Inventory by Warehouses'))
            ->icon('heroicon-o-home-modern')
            ->schema([
              Repeater::make('stocks')
                ->label(__('Warehouse Allocations'))
                ->relationship('stocks')
                ->schema([
                  Select::make('warehouse_id')
                    ->label(__('Warehouse'))
                    ->relationship('warehouse', 'name')
                    ->required()
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                  TextInput::make('quantity')
                    ->label(__('Physical Quantity'))
                    ->numeric()
                    ->default(0)
                    ->required(),

                  TextInput::make('reserved')
                    ->label(__('Reserved'))
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(__('Locked by active orders')),
                ])
                ->columns(3)
                ->defaultItems(0)
                ->addActionLabel(__('Add Warehouse Stock')),
            ]),

          MediaGalleryTab::make(),

          SalesChannelsTab::make('product_variant'),
        ])
        ->columnSpanFull(),
    ]);
  }
}
