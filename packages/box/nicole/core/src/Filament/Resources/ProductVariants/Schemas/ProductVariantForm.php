<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\ProductVariants\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
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
            ->schema(function (Get $get) {
              $productId = $get('product_id');
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
              Section::make(__('Base Cost (COGS)'))
                ->description(
                  __('Physical purchasing cost and currency for this SKU.'),
                )
                ->schema([
                  TextInput::make('cost_price')
                    ->label(__('Cost Price'))
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                      $costPrice = (float) $state;
                      $prices = $get('prices') ?? [];

                      foreach ($prices as $key => $priceData) {
                        $markup = (float) ($priceData['markup_percent'] ?? 0);
                        if ($markup > 0) {
                          $set(
                            "prices.{$key}.price",
                            round($costPrice * (1 + $markup / 100), 2),
                          );
                        }
                      }
                    })
                    ->required(),

                  Select::make('currency')
                    ->label(__('Currency'))
                    ->options(
                      fn () => Currency::pluck('code', 'code')->toArray(),
                    )
                    ->default('RUB')
                    ->searchable()
                    ->required(),
                ])
                ->columns(2),

              Repeater::make('prices')
                ->label(__('Sales Pricing Matrix'))
                ->relationship('prices')
                ->schema([
                  Select::make('price_type_id')
                    ->label(__('Price Type'))
                    ->relationship('type', 'name')
                    ->required()
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                  TextInput::make('markup_percent')
                    ->label(__('Markup (%)'))
                    ->numeric()
                    ->suffix('%')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                      $costPrice = (float) $get('../../cost_price');
                      $costCurrency = $get('../../currency');
                      $priceTypeId = $get('price_type_id');
                      $markup = (float) $state;

                      if ($costPrice > 0 && $priceTypeId && $costCurrency) {
                        $priceType = PriceType::with('currency')->find(
                          $priceTypeId,
                        );
                        $targetCurrency = $priceType?->currency?->code ?? 'RUB';

                        $convertedCost = app(PricingManager::class)->convert(
                          $costPrice,
                          $costCurrency,
                          $targetCurrency,
                        );

                        $set(
                          'price',
                          round($convertedCost * (1 + $markup / 100), 2),
                        );
                      }
                    }),

                  TextInput::make('price')
                    ->label(__('Final Price'))
                    ->numeric()
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                      $costPrice = (float) $get('../../cost_price');
                      $costCurrency = $get('../../currency');
                      $priceTypeId = $get('price_type_id');
                      $finalPrice = (float) $state;

                      if (
                        $costPrice > 0 &&
                        $finalPrice > 0 &&
                        $priceTypeId &&
                        $costCurrency
                      ) {
                        $priceType = PriceType::with('currency')->find(
                          $priceTypeId,
                        );
                        $targetCurrency = $priceType?->currency?->code ?? 'RUB';

                        $convertedCost = app(PricingManager::class)->convert(
                          $costPrice,
                          $costCurrency,
                          $targetCurrency,
                        );

                        $markup = ($finalPrice / $convertedCost - 1) * 100;
                        $set('markup_percent', round($markup, 2));
                      } elseif ($costPrice == 0 && $finalPrice > 0) {
                        $set('markup_percent', 0);
                      }
                    })
                    ->helperText(__('Base selling price (in system currency)')),
                ]),
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
