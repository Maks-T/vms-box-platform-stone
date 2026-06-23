<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\ProductVariants\Schemas\Tabs;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductVariant;
use Nicole\Box\Core\Services\PricingManager;
use Nicole\Box\Core\Filament\Resources\ProductVariants\Schemas\Components\BaseCostSection;
use Nicole\Box\Core\Filament\Resources\ProductVariants\Schemas\Components\PricesRepeater;

class PricingTab
{
  /**
   * Вспомогательный метод для обновления итоговой цены в строке репитера.
   */
  public static function updatePriceItem(Get $get, Set $set, string $inputCurrency, float $markup): void
  {
    $costPrice = (float) $get('../../cost_price');
    $costCurrency = $get('../../currency') ?? 'USD';

    if ($costPrice > 0) {
      $priceInCostCurrency = $costPrice * (1 + $markup / 100);
      $converted = app(PricingManager::class)->convert($priceInCostCurrency, $costCurrency, $inputCurrency);
      $set('desired_price', round($converted, 2));
    } else {
      $set('desired_price', 0.00);
    }
  }

  public static function make(): Tab
  {
    return Tab::make(__('Pricing & Economy'))
      ->icon('heroicon-o-banknotes')
      ->schema([
        // Подключаем декомпозированные компоненты
        BaseCostSection::make(),
        PricesRepeater::make(),

        // 5. Текстовый блок со справочной информацией (Тип аргумента изменен на ProductVariant для исправления IDE)
        TextEntry::make('calculated_dictionary_info')
          ->hiddenLabel()
          ->columnSpanFull()
          ->visible(function (Get $get, ?ProductVariant $record) {
            $productId = $get('product_id') ?? $record?->product_id;
            if (!$productId) return false;
            $product = Product::with('type')->find($productId);
            $isComplex = $product?->type?->pricing_mode === 'complex_dictionary';
            return $isComplex && !$get('is_manual_pricing');
          })
          ->state(function (Get $get, ?ProductVariant $record) {
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
      ]);
  }
}
