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
    $retailPriceId = PriceType::where('slug', 'retail')->value('id');

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
        ->state(function (Product $record) use ($slug, $retailPriceId) {

          $variant = $record->variants->first(function ($v) use ($slug) {
            return $v->attributeValues->contains(fn ($av) => $av->option?->slug === $slug);
          });

          if (!$variant || !$retailPriceId) {
            return null;
          }

          $price = app(PricingManager::class)->getVariantPrice($variant, 'retail');

          return $price > 0 ? $price : null;
        })
        ->updateStateUsing(function (Product $record, $state) use (
          $slug,
          $retailPriceId,
        ) {
          if (!$retailPriceId || $state === null) {
            return;
          }

          $variant = $record->variants->first(function ($v) use ($slug) {
            return $v->attributeValues->contains(fn ($av) => $av->option?->slug === $slug);
          });

          if (!$variant) {
            return;
          }

          $costPrice = (float) $variant->cost_price;
          $newPrice = (float) $state;

          // Динамически вычисляем наценку на основе себестоимости услуги
          if ($costPrice <= 0) {
            // Если себестоимости нет, приравниваем ее к введенной цене, а маржу ставим в 0%
            $variant->update(['cost_price' => $newPrice]);
            $markup = 0.0;
          } else {
            // Если себестоимость есть, вычисляем точную наценку относительно нее
            $markup = (($newPrice / $costPrice) - 1) * 100;
          }

          // Сохраняем только процент наценки высокой точности в БД
          ProductVariantPrice::updateOrCreate(
            [
              'product_variant_id' => $variant->id,
              'price_type_id' => $retailPriceId,
            ],
            ['markup_percent' => round($markup, 10)]
          );

          // Обновляем минимальную цену товара для индекса каталога
          $record->refreshMinPrice();
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
