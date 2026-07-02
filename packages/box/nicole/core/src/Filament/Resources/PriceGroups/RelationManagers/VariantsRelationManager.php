<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\PriceGroups\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Nicole\Box\Core\Models\ProductVariant;

class VariantsRelationManager extends RelationManager
{
  protected static string $relationship = 'variants';

  public function form(Schema $schema): Schema
  {
    return $schema->components([]);
  }

  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('sku')
      ->columns([
        ImageColumn::make('preview_image')
          ->label(__('Photo'))
          ->state(function (ProductVariant $record) {
            return $record->getPreviewUrl() ?? null;
          })
          ->circular()
          ->imageWidth(45)
          ->imageHeight(45),

        TextColumn::make('product.name')
          ->label(__('Parent Product'))
          ->state(fn (ProductVariant $record) => $record->product?->name ?? '—')
          ->weight('bold')
          ->searchable(),

        TextColumn::make('sku')
          ->label('SKU')
          ->fontFamily('mono')
          ->color('gray')
          ->searchable(),

        TextColumn::make('stock')
          ->label(__('Stock'))
          ->numeric()
          ->badge()
          ->state(fn (ProductVariant $record) => $record->stock)
          ->color(fn ($state) => $state <= 0 ? 'danger' : 'success')
          ->alignEnd(),
      ])
      ->filters([
        //
      ])
      ->headerActions([
        AssociateAction::make()
          ->preloadRecordSelect()
          ->recordSelectSearchColumns(['sku', 'product.name']),
      ])

      ->recordActions([
        DissociateAction::make(),
      ])

      ->toolbarActions([
        BulkActionGroup::make([
          DissociateBulkAction::make(),
        ]),
      ]);
  }
}
