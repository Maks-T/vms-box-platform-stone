<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\SettingSchemas\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Config;

class SettingSchemaForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema->components([
      Section::make(__('Channel Settings Schema Builder'))
        ->description(__('Define which fields will be available for this entity in different Sales Channels.'))
        ->schema([

          Select::make('entity_type')
            ->label(__('Target Entity'))
            ->options(function () {
              
              $entities = Config::get('nicole.setting_entities', []);
              return collect($entities)
                ->map(fn(string $label) => __($label))
                ->toArray();
            })
            ->required()
            ->unique(ignoreRecord: true)
            ->native(false),

          
          Repeater::make('meta_schema')
            ->label(__('Fields Configuration'))
            ->schema([
              TextInput::make('key')
                ->label(__('Key (System)'))
                ->placeholder('is_collapsed')
                ->required()
                ->alphaDash(),

              TextInput::make('label')
                ->label(__('Label (Human readable)'))
                ->required()
                ->translatable(),

              Select::make('type')
                ->label(__('Field Type'))
                ->options([
                  'text' => __('String'),
                  'number' => __('Numeric'),
                  'boolean' => __('Boolean (Toggle)'),
                  'select' => __('Dictionary (Select)'),
                ])
                ->required()
                ->live()
                ->native(false),

              Select::make('width')
                ->label(__('UI Width'))
                ->options([
                  1 => __('Minimum Part'),
                  2 => __('Full Width'),
                ])
                ->default(1),

              Repeater::make('options')
                ->label(__('Dictionary Options'))
                ->visible(fn($get) => $get('type') === 'select')
                ->schema([
                  TextInput::make('key')
                    ->label(__('Value (System)'))
                    ->required()
                    ->alphaDash(),

                  TextInput::make('label')
                    ->label(__('Label (Human readable)'))
                    ->required()
                    ->translatable(),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->addActionLabel(__('Add Option'))
                ->reorderable(false)
                
                ->formatStateUsing(function ($state) {
                  if (!is_array($state)) return [];
                  $result = [];
                  foreach ($state as $key => $label) {
                    $result[] = [
                      'key' => $key,
                      'label' => $label,
                    ];
                  }
                  return $result;
                })
                
                ->dehydrateStateUsing(function ($state) {
                  $result = [];
                  if (!is_array($state)) return $result;
                  foreach ($state as $item) {
                    if (!empty($item['key'])) {
                      $result[$item['key']] = $item['label'] ?? $item['key'];
                    }
                  }
                  return $result;
                }),
            ])
            ->columns(4)
            ->addActionLabel(__('Add Field'))
            ->reorderable()
            ->collapsible()
            ->itemLabel(fn(array $state): ?string => $state['key'] ?? null),
        ]),
    ]);
  }
}
