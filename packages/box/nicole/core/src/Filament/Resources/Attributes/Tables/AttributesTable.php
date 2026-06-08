<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\Attributes\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Nicole\Box\Core\Models\Attribute;
use Nicole\Box\Core\Support\Filament\FilterHelper;
use Nicole\Box\Core\Support\Filament\TableHelper;

class AttributesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(['name', 'code'])
                    ->sortable(),

                TableHelper::codeColumn('code'), // Shared

                TextColumn::make('type')->label(__('Type'))->badge()->toggleable(),

                TableHelper::statusColumn(), // Shared
            ])
            ->filters([
                FilterHelper::selectFilter('type', __('Type'), [
                    Attribute::TYPE_STRING => __('String'),
                    Attribute::TYPE_NUMERIC => __('Numeric'),
                    Attribute::TYPE_BOOLEAN => __('Boolean (Toggle)'),
                    Attribute::TYPE_DICTIONARY => __('Dictionary (Select)'),
                    Attribute::TYPE_COMPLEX => __('Complex Dictionary'),
                ]),

                FilterHelper::activeFilter(), // Shared
            ]);
    }
}
