<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\ProductVariants\Schemas\Tabs;

use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
use Nicole\Box\Core\Filament\Concerns\HasDynamicEavFields;
use Nicole\Box\Core\Models\Product;

class TechnicalSpecsTab
{
  use HasDynamicEavFields;

  public static function make(): Tab
  {
    return Tab::make(__('Technical Specifications'))
      ->icon('heroicon-o-adjustments-vertical')
      ->schema(function (Get $get, ?Model $record) {
        $productId = $get('product_id') ?? $record?->product_id;
        if (! $productId) {
          return [];
        }

        $productType = Product::find($productId)?->product_type_id;

        // Метод getDynamicEavSchema импортирован из трейта HasDynamicEavFields
        return static::getDynamicEavSchema($productType, 'product_variant');
      })
      ->columns(3);
  }
}
