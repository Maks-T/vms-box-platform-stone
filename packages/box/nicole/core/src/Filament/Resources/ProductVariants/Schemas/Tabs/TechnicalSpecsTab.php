<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\ProductVariants\Schemas\Tabs;

use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
use Nicole\Box\Core\Filament\Concerns\HasDynamicEavFields;
use Nicole\Box\Core\Models\Product;
use Livewire\Component; // <-- Импортируем базовый класс Livewire-компонента
use Nicole\Box\Core\Filament\Resources\ProductVariants\Schemas\ProductVariantForm; // <-- Импортируем форму для доступа к хелперу

class TechnicalSpecsTab
{
  use HasDynamicEavFields;

  public static function make(): Tab
  {
    return Tab::make(__('Technical Specifications'))
      ->icon('heroicon-o-adjustments-vertical')
      ->schema(function (Get $get, ?Model $record, Component $livewire) {

        $product = ProductVariantForm::resolveProduct($get, $record, $livewire);

        $productType = $product?->product_type_id;

        // Метод getDynamicEavSchema импортирован из трейта HasDynamicEavFields
        return static::getDynamicEavSchema($productType, 'product_variant');
      })
      ->columns(3);
  }
}
