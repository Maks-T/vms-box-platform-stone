<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\ComplexDictionaries\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Nicole\Box\Core\Filament\Forms\Tabs\SalesChannelsTab;
use Nicole\Box\Core\Filament\Helpers\FormHelper;

class ComplexDictionaryForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema->components([
      Tabs::make('DictionaryTabs')
        ->tabs([

          Tabs\Tab::make(__('Dictionary Identity'))
            ->icon('heroicon-o-identification')
            ->schema([
              Section::make()
                ->schema([
                  TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                      FormHelper::generateSlug('code', '_', false),
                    )
                    ->translatable(),

                  TextInput::make('code')
                    ->label(__('Code'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->alphaDash(),

                  Toggle::make('is_active')
                    ->label(__('Is Active'))
                    ->default(true)
                    ->columnSpanFull(),
                ])
                ->columns(2),
            ]),

          SalesChannelsTab::make('complex_dictionary'),

          Tabs\Tab::make(__('Schema Builder')) 
          ->icon('heroicon-o-rectangle-group') 
          ->schema([
            Section::make(__('Dictionary Fields Schema'))
                ->description(__('Define the dynamic fields that this dictionary will store (e.g. min_size, price_material).'))
                ->schema([
                  
                  Repeater::make('meta_schema')
                    ->hiddenLabel()
                    ->schema([
                      TextInput::make('key')
                        ->label(__('Key (System)'))
                        ->placeholder('material_cost')
                        ->required()
                        ->alphaDash(),

                      Select::make('type')
                        ->label(__('Field Type'))
                        ->options([
                          'text' => __('String'),
                          'number' => __('Numeric'),
                          'boolean' => __('Boolean (Checkbox)'),
                          'price' => __('Price & Markup'),
                        ])
                        ->required()
                        ->native(false),

                      Select::make('currency')
                        ->label(__('Currency'))
                        ->options(\Nicole\Box\Core\Models\Currency::where('is_active', true)->pluck('code', 'code'))
                        ->default('USD')
                        ->visible(fn (Get $get) => $get('type') === 'price')
                        ->required(fn (Get $get) => $get('type') === 'price'),

                      TextInput::make('label')
                        ->label(__('Label (Human readable)'))
                        ->required()
                        ->translatable(),

                      Toggle::make('is_public')
                        ->label(__('Public API Field'))
                        ->helperText(__('Master switch for this field visibility'))
                        ->default(true),
                    ])
                    ->columns(2)
                    ->reorderable()
                    ->collapsible()
                    ->addActionLabel(__('Add Field')),
                ]),
            ]),
        ])
        ->columnSpanFull(),
    ]);
  }
}
