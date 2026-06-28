<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Nicole\Box\Core\Models\OrderProduct;

class ProductsRelationManager extends RelationManager
{
  protected static string $relationship = 'products';

  public static function getTitle(Model $ownerRecord, string $pageClass): string
  {
    return __('Catalog Products');
  }

  /**
   * Таблица товаров, используемых в заказе
   */
  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('variant.sku')
      ->columns([
        ImageColumn::make('variant.preview_image')
          ->label(__('Photo'))
          ->state(function (OrderProduct $record) {
            return $record->variant?->getPreviewUrl() ?? null;
          })
          ->circular()
          ->imageWidth(60)
          ->imageHeight(60),

        TextColumn::make('variant.product.name')
          ->label(__('Name'))
          ->state(fn (OrderProduct $record) => $record->variant?->product?->name ?? '—')
          ->weight('bold')
          ->searchable(),

        TextColumn::make('variant.sku')
          ->label(__('SKU'))
          ->fontFamily('mono')
          ->color('gray')
          ->searchable(),

        TextColumn::make('quantity')
          ->label(__('Qty'))
          ->state(function (OrderProduct $record) {
            $unitSymbol = $record->variant?->product?->unit?->symbol ?? 'шт.';
            return (float)$record->quantity . ' ' . $unitSymbol;
          })
          ->weight('bold')
          ->color('primary')
          ->alignEnd(),

        TextColumn::make('section.title')
          ->label(__('Order Section'))
          ->state(fn (OrderProduct $record) => $record->section?->title ?? '—')
          ->color('gray')
          ->toggleable(),
      ])
      ->filters([
      ])
      ->recordActions([
      ])
      ->toolbarActions([
      ])
      ->defaultSort('id', 'asc');
  }
}
