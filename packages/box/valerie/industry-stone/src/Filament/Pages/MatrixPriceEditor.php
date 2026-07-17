<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Filament\Notifications\Notification;
use Nicole\Box\Core\Filament\Resources\Products\ProductResource;
use Nicole\Box\Core\Models\AttributeOption;
use Nicole\Box\Core\Models\Product;
use Valerie\Box\IndustryStone\Services\ServiceMatrixTransferService;
use Valerie\Box\IndustryStone\Filament\Helpers\ServiceMatrixTableHelper;

class MatrixPriceEditor extends Page implements HasForms, HasTable
{
  use InteractsWithForms;
  use InteractsWithTable;

  protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-table-cells';

  protected string $view = 'valerie-stone::filament.pages.matrix-price-editor';

  protected static ?string $slug = 'services-matrix';

  protected static ?int $navigationSort = 4;

  public static function getNavigationGroup(): ?string
  {
    return __('Catalog');
  }

  public static function getNavigationLabel(): string
  {
    return __('Services Price Matrix');
  }

  public function getTitle(): string
  {
    return __('Processing Services Price List');
  }

  /**
   * Получение активных материалов
   */
  protected function getActiveMaterials()
  {
    return AttributeOption::whereHas(
      'attribute',
      fn($q) => $q->where('code', 'target_material'),
    )
      ->get()
      ->keyBy('slug');
  }

  /**
   * Страничные действия (шапка страницы)
   */
  protected function getHeaderActions(): array
  {
    $materials = $this->getActiveMaterials();

    return [
      // 1. ДЕЙСТВИЕ: ЭКСПОРТ
      Action::make('export_prices')
        ->label('Экспорт цен')
        ->icon('heroicon-o-arrow-down-tray')
        ->color('gray')
        ->action(function (ServiceMatrixTransferService $transferService) use ($materials) {
          $services = Product::where('catalog_type', 'service')->get();

          return $transferService->export($services, $materials);
        }),


      Action::make('import_prices')
        ->label('Импорт цен')
        ->icon('heroicon-o-arrow-up-tray')
        ->color('warning')
        ->schema([
          FileUpload::make('file')
            ->label('Выберите файл импорта (CSV)')
            ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'text/plain'])
            ->required()
            ->disk('local')
            ->directory('temp/imports'),
        ])
        ->action(function (array $data, ServiceMatrixTransferService $transferService) use ($materials) {
          try {
            $updatedCount = $transferService->import($data['file'], $materials);

            Notification::make()
              ->success()
              ->title('Импорт успешно завершен')
              ->body("Обновлены себестоимости и наценки для {$updatedCount} услуг обработки.")
              ->send();
          } catch (\Throwable $e) {
            Notification::make()
              ->danger()
              ->title('Ошибка импорта')
              ->body($e->getMessage())
              ->persistent()
              ->send();
          }
        }),
    ];
  }

  /**
   * Конфигурация таблицы на странице
   */
  public function table(Table $table): Table
  {
    $materials = $this->getActiveMaterials();

    return $table
      ->query(
        Product::query()
          ->where('catalog_type', 'service')
          ->with([
            'media',
            'category',
            'variants.attributeValues.option',
            'variants.prices',
          ]),
      )
      ->columns(array_merge([

        TextColumn::make('name')
          ->label(__('Service / Work'))
          ->state(function (Product $record) {
            return $record->getTranslation('name', app()->getLocale())
              ?? ($record->getTranslation('name', 'ru') ?? '-');
          })
          ->searchable()
          ->sortable()
          ->weight('medium')
          ->toggleable()
          ->wrap(),

        TextColumn::make('code')
          ->label(__('Code') . ' / Артикул')
          ->searchable()
          ->fontFamily('mono')
          ->color('gray')
          ->copyable()
          ->toggleable(isToggledHiddenByDefault: true),

        TextColumn::make('slug')
          ->label('Идентификатор (Slug)')
          ->searchable()
          ->fontFamily('mono')
          ->color('gray')
          ->copyable()
          ->toggleable(isToggledHiddenByDefault: true),

        TextColumn::make('category.name')
          ->label(__('Category'))
          ->state(function (Product $record) {
            return $record->category?->getTranslation('name', app()->getLocale())
              ?? ($record->category?->getTranslation('name', 'ru') ?? '-');
          })
          ->badge()
          ->color('gray')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ], ServiceMatrixTableHelper::buildMaterialColumns($materials))) // Подключение динамических колонок материалов
      ->columnManagerColumns(2) // Сетка столбцов в 2 колонки
      ->groups([
        Group::make('category.name')
          ->label(__('Category'))
          ->collapsible()
          ->titlePrefixedWithLabel(false)
          ->getTitleFromRecordUsing(function (Product $record) {
            $categoryName = $record->category?->getTranslation('name', app()->getLocale())
              ?? ($record->category?->getTranslation('name', 'ru') ?? '—');
            return mb_strtoupper($categoryName);
          })
      ])
      ->defaultGroup('category.name')
      ->filters([
        SelectFilter::make('category_id')
          ->label(__('Category'))
          ->relationship(
            'category',
            'name',
            fn($query) => $query->whereHas(
              'products',
              fn($q) => $q->where('catalog_type', 'service'),
            ),
          )
          ->multiple()
          ->preload(),

        TernaryFilter::make('is_active')->label(__('Is Active'))->native(false),
      ])
      ->searchable()
      ->recordActions([
        Action::make('edit_service')
          ->label(__('Details'))
          ->icon('heroicon-o-pencil-square')
          ->color('gray')
          ->tooltip(__('Open service details'))
          ->url(
            fn(Product $record): string => ProductResource::getUrl('edit', [
              'record' => $record,
            ]),
          )
          ->openUrlInNewTab(),
      ])
      ->striped()
      ->paginationPageOptions([25, 50, 100])
      ->defaultPaginationPageOption(50);
  }
}
