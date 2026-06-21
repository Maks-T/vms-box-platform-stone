<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\ComplexDictionaries\RelationManagers;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Nicole\Box\Core\Filament\Forms\Tabs\SalesChannelsTab;
use Nicole\Box\Core\Filament\Helpers\FormHelper;
use Nicole\Box\Core\Models\ComplexDictionary;
use Nicole\Box\Core\Models\PriceType;

class RecordsRelationManager extends RelationManager
{
  protected static string $relationship = 'records';

  public static function getTitle(Model $ownerRecord, string $pageClass): string
  {
    return __('Dictionary Records');
  }

  protected function getProcessedFields(): array
  {
    $ownerRecord = $this->getOwnerRecord();
    $schemaFields = $ownerRecord->meta_schema ?? [];
    $processed = [];

    foreach ($schemaFields as $field) {
      $key = $field['key'];
      $label = is_array($field['label'])
        ? $field['label'][app()->getLocale()] ?? (collect($field['label'])->first() ?? $key)
        : $field['label'];

      $processed[] = [
        'key' => $key,
        'type' => $field['type'],
        'label' => $label,
        'payloadKey' => "meta.{$key}",
      ];
    }

    return $processed;
  }

  public function form(Schema $schema): Schema
  {
    $dynamicComponents = [];
    $priceTypes = PriceType::all(); // Динамически получаем все типы цен в системе

    foreach ($this->getProcessedFields() as $field) {
      if ($field['type'] === ComplexDictionary::FIELD_TYPE_PRICE) {
        $priceTypeComponents = [
          // Поле базовой себестоимости закупки
          TextInput::make($field['payloadKey'])
            ->label(__('Base Cost'))
            ->numeric()
            ->default(0)
            ->columnSpanFull(),
        ];

        // Автоматически генерируем поля ввода наценки для каждого типа цен из БД
        foreach ($priceTypes as $type) {
          $priceTypeComponents[] = TextInput::make("meta.{$field['key']}_markup_{$type->slug}")
            ->label(__('Markup for :type (%)', ['type' => (string) $type->name]))
            ->numeric()
            ->suffix('%')
            ->default(0);
        }

        $dynamicComponents[] = Fieldset::make((string) $field['label'])
          ->schema($priceTypeComponents)
          ->columns(2);
      } else {
        $input = match ($field['type']) {
          'boolean' => Toggle::make($field['payloadKey'])->inline(false),
          'number' => TextInput::make($field['payloadKey'])->numeric(),
          default => TextInput::make($field['payloadKey']),
        };
        $dynamicComponents[] = $input->label((string) $field['label']);
      }
    }

    return $schema->components([
      Tabs::make('RecordModalTabs')
        ->tabs([
          Tab::make(__('Data'))
            ->icon('heroicon-o-document-text')
            ->schema([
              Section::make(__('Record Identity'))
                ->schema([
                  TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(FormHelper::generateSlug('slug', '-', false))
                    ->translatable(),

                  TextInput::make('slug')
                    ->label(__('Slug'))
                    ->required()
                    ->alphaDash(),

                  TextInput::make('external_code')
                    ->label(__('External Code'))
                    ->nullable(),

                  Toggle::make('is_active')
                    ->label(__('Is Active'))
                    ->default(true),
                ])
                ->columns(2),

              Section::make(__('Additional Data'))->schema($dynamicComponents),
            ]),
          SalesChannelsTab::make('complex_dictionary_record'),
        ])
        ->columnSpanFull(),
    ]);
  }

  public function table(Table $table): Table
  {
    $columns = [
      TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
    ];

    $priceTypes = PriceType::all();

    foreach ($this->getProcessedFields() as $field) {
      if ($field['type'] === ComplexDictionary::FIELD_TYPE_PRICE) {
        // Базовая себестоимость (всегда видна)
        $columns[] = TextColumn::make($field['payloadKey'])
          ->label($field['label'])
          ->numeric(2)
          ->sortable();

        // Динамически генерируем колонки наценки и итоговой цены для каждого типа цен в системе
        foreach ($priceTypes as $type) {
          $isRetail = $type->slug === 'retail';

          // Колонка наценки (скрыта по умолчанию для всех кроме розницы)
          $columns[] = TextColumn::make("meta.{$field['key']}_markup_{$type->slug}")
            ->label(__('Markup :type', ['type' => (string) $type->name]))
            ->suffix('%')
            ->color('gray')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: !$isRetail);

          // Колонка итоговой цены (скрыта по умолчанию для всех кроме розницы)
          $columns[] = TextColumn::make("calculated_{$field['key']}_{$type->slug}")
            ->label(__('Total :type', ['type' => (string) $type->name]))
            ->state(function (Model $record) use ($field, $type) {
              $meta = $record->meta ?? [];
              $cost = (float) ($meta[$field['key']] ?? 0);
              $markup = (float) ($meta["{$field['key']}_markup_{$type->slug}"] ?? 0);

              if ($cost > 0) {
                return number_format($cost * (1 + $markup / 100), 2, '.', ' ');
              }
              return '—';
            })
            ->color('success')
            ->weight('bold')
            ->toggleable(isToggledHiddenByDefault: !$isRetail);
        }
      } elseif ($field['type'] === 'boolean') {
        $columns[] = IconColumn::make($field['payloadKey'])
          ->label($field['label'])
          ->boolean()
          ->toggleable();
      } elseif ($field['type'] === 'number') {
        $columns[] = TextColumn::make($field['payloadKey'])
          ->label($field['label'])
          ->numeric()
          ->sortable()
          ->toggleable();
      } else {
        $columns[] = TextColumn::make($field['payloadKey'])
          ->label($field['label'])
          ->searchable()
          ->toggleable();
      }
    }

    $columns[] = IconColumn::make('is_active')->label(__('Is Active'))->boolean();
    $columns[] = TextColumn::make('sort_order')->label(__('Sort'))->numeric()->sortable();

    return $table
      ->recordTitleAttribute('name')
      ->columns($columns)
      ->reorderable('sort_order')
      ->defaultSort('sort_order', 'asc')
      ->headerActions([CreateAction::make()->modalWidth(Width::SevenExtraLarge)])
      ->recordActions([EditAction::make()->modalWidth(Width::SevenExtraLarge), DeleteAction::make()])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make(),
          BulkAction::make('activate')
            ->label(__('Activate'))
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
          BulkAction::make('deactivate')
            ->label(__('Deactivate'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
        ]),
      ]);
  }
}
