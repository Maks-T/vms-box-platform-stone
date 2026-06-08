<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\Currencies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Nicole\Box\Core\Support\Filament\FilterHelper;
use Nicole\Box\Core\Support\Filament\ProtectDefaultRecord;
use Nicole\Box\Core\Support\Filament\TableHelper;

class CurrenciesTable
{
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
        TextColumn::make('code')->label(__('Code'))->searchable()->badge(),

        
        TextColumn::make('rate')->label(__('Rate'))->numeric(4)->sortable(),

        IconColumn::make('is_default')->label(__('Base'))->boolean(),
        TextColumn::make('symbol')->label(__('Symbol')),

        TableHelper::statusColumn(), 
      ])
      ->filters([
        FilterHelper::activeFilter(), 
      ])
      
      ->reorderable('sort_order')
      ->defaultSort('sort_order', 'asc')
      ->recordActions([
        EditAction::make(),
        ProtectDefaultRecord::tableDeleteAction('Cannot delete base currency'),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          ProtectDefaultRecord::tableBulkDeleteAction('Base currencies skipped'),
        ]),
      ]);
  }
}
