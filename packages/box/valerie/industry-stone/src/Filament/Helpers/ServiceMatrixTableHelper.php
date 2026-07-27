<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Filament\Helpers;

use Filament\Tables\Columns\TextInputColumn;
use Illuminate\Support\Collection;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Services\PricingManager;

class ServiceMatrixTableHelper
{
  /**
   * Сборка динамического списка колонок материалов
   */
  public static function buildMaterialColumns(Collection $materials): array
  {
    $columns = [];

    foreach ($materials as $slug => $option) {
      $columns[] = TextInputColumn::make("mat_{$slug}")
        ->label((string)$option->value)
        ->alignCenter()
        ->type('number')
        ->toggleable()
        ->disabled(function (Product $record) use ($slug) {
          return !$record->variants->contains(function ($v) use ($slug) {
            return $v->attributeValues->contains(fn($av) => $av->option?->slug === $slug);
          });
        })
        ->state(function (Product $record) use ($slug) {
          $variant = $record->variants->first(function ($v) use ($slug) {
            return $v->attributeValues->contains(fn($av) => $av->option?->slug === $slug);
          });

          return $variant ? $variant->cost_price : null;
        })
        ->suffix(function (Product $record) use ($slug) {
          $variant = $record->variants->first(function ($v) use ($slug) {
            return $v->attributeValues->contains(fn ($av) => $av->option?->slug === $slug);
          });

          if (!$variant) {
            return null;
          }

          $pricingManager = app(PricingManager::class);
          $retailPrice = $pricingManager->getVariantPrice($variant, 'retail');
          $currencySymbol = $pricingManager->baseCurrency->symbol_native
            ?? ($pricingManager->baseCurrency->symbol ?? 'Br');

          return '→ ' . number_format($retailPrice, 0, '.', ' ') . ' ' . $currencySymbol;
        })
        ->updateStateUsing(function (Product $record, $state) use ($slug) {
          if ($state === null) {
            return;
          }

          $variant = $record->variants->first(function ($v) use ($slug) {
            return $v->attributeValues->contains(fn($av) => $av->option?->slug === $slug);
          });

          if ($variant) {
            $variant->update([
              'cost_price' => (float)$state,
              'is_manual_pricing' => true,
            ]);
            $record->refreshMinPrice();
          }
        });
    }

    return $columns;
  }
}
