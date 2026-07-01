<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Nicole\Box\Core\Filament\Resources\Products\ProductResource;
use Nicole\Box\Core\Models\AttributeOption;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductVariantPrice;
use Nicole\Box\Core\Services\PricingManager;

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

  public function table(Table $table): Table
  {
     // Получаем материалы для динамических колонок (Акрил, Кварц)
    $materials = AttributeOption::whereHas(
      'attribute',
      fn ($q) => $q->where('code', 'target_material'),
    )
      ->get()
      ->keyBy('slug');

    $columns = [
      ImageColumn::make('image')
        ->label(__('Photo'))
        ->state(function (Product $record) {
          $url = $record->getPreviewUrl();
          if (! $url) {
            return null;
          }

          return str_starts_with($url, 'http') ? $url : url($url);
        })
        ->circular()
        ->toggleable(),

      TextColumn::make('name')
        ->label(__('Service / Work'))
        ->searchable()
        ->sortable()
        ->weight('medium')
        ->wrap(),

      TextColumn::make('slug')
        ->label(__('Slug'))
        ->searchable()
        ->fontFamily('mono')
        ->color('gray')
        ->copyable()
        ->toggleable(isToggledHiddenByDefault: true),

      TextColumn::make('category.name')
        ->label(__('Category'))
        ->badge()
        ->color('gray')
        ->sortable()
        ->toggleable(),
    ];

    foreach ($materials as $slug => $option) {
      $columns[] = TextInputColumn::make("mat_{$slug}")
        ->label((string) $option->value)
        ->alignCenter()
        ->type('number')
        ->toggleable()
        ->disabled(function (Product $record) use ($slug) {
          return ! $record->variants->contains(function ($v) use ($slug) {
            return $v->attributeValues->contains(fn ($av) => $av->option?->slug === $slug);
          });
        })

        ->state(function (Product $record) use ($slug) {
          $variant = $record->variants->first(function ($v) use ($slug) {
            return $v->attributeValues->contains(fn ($av) => $av->option?->slug === $slug);
          });

          if (! $variant) {
            return null;
          }

          return app(PricingManager::class)->getVariantPrice($variant, 'retail');
        })

        ->updateStateUsing(function (Product $record, $state) use ($slug) {
          if ($state === null) {
            return;
          }

          $variant = $record->variants->first(function ($v) use ($slug) {
            return $v->attributeValues->contains(fn ($av) => $av->option?->slug === $slug);
          });

          if (! $variant) {
            return;
          }

          $variant->update([
            'cost_price' => (float) $state,
            'is_manual_pricing' => true,
          ]);
        });
    }

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
      ->columns($columns)
      ->filters([
        SelectFilter::make('category_id')
          ->label(__('Category'))
          ->relationship(
            'category',
            'name',
            fn ($query) => $query->whereHas(
              'products',
              fn ($q) => $q->where('catalog_type', 'service'),
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
            fn (Product $record): string => ProductResource::getUrl('edit', [
              'record' => $record,
            ]),
          )
          ->openUrlInNewTab(),
      ])
      ->striped()
      ->defaultSort('category_id')
      ->paginationPageOptions([25, 50, 100])
      ->defaultPaginationPageOption(50);
  }
}
