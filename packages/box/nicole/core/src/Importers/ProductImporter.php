<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Importers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Nicole\Box\Core\Importers\Contracts\ImportModuleInterface;
use Nicole\Box\Core\Models\Attribute;
use Nicole\Box\Core\Models\AttributeOption;
use Nicole\Box\Core\Models\Category;
use Nicole\Box\Core\Models\ComplexDictionaryRecord;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductAttributeValue;
use Nicole\Box\Core\Models\ProductType;
use Nicole\Box\Core\Models\ProductVariant;
use Nicole\Box\Core\Models\Unit;

class ProductImporter implements ImportModuleInterface
{
  private array $mapTypes = [];
  private array $mapCategories = [];
  private array $mapAttributes = [];
  private array $mapOptions = [];
  private array $mapComplexRecords = [];
  private array $mapUnits = [];

  public function getName(): string
  {
    return 'Products, Variants & EAV';
  }

  public function run(array $settings, array $data, Command $command): void
  {
    $retailPriceId = \Nicole\Box\Core\Models\PriceType::where('slug', 'retail')->value('id') ?? 1;

    $mainWarehouse = \Nicole\Box\Core\Models\Warehouse::firstOrCreate(
      ['external_code' => 'wh_main'],
      [
        'slug' => 'main',
        'name' => ['ru' => 'Главный склад', 'en' => 'Main Warehouse'],
        'is_active' => true,
      ]
    );

    $products = $data['products'] ?? [];
    if (empty($products)) {
      return;
    }

    $this->warmUpCache();
    $bar = $command->getOutput()->createProgressBar(count($products));

    foreach ($products as $item) {
      $typeId = $this->mapTypes[$item['product_type_external_code'] ?? ''] ?? null;
      $categoryId = $this->mapCategories[$item['category_external_code'] ?? ''] ?? null;

      
      $unitCode = $item['unit_code'] ?? 'pcs';
      if (!isset($this->mapUnits[$unitCode])) {
        $unitName = match($unitCode) {
          'm' => ['ru' => 'м.п.', 'en' => 'rm'],
          'set' => ['ru' => 'компл.', 'en' => 'set'],
          'srv' => ['ru' => 'усл.', 'en' => 'srv'],
          'km' => ['ru' => 'км', 'en' => 'km'],
          'floor' => ['ru' => 'этаж', 'en' => 'floor'],
          default => ['ru' => 'Шт.', 'en' => 'Pcs'],
        };
        $newUnit = Unit::firstOrCreate(
          ['slug' => $unitCode],
          ['name' => $unitName, 'symbol' => $unitName, 'code' => $unitCode, 'is_active' => true]
        );
        $this->mapUnits[$unitCode] = $newUnit->id;
      }
      $unitId = $this->mapUnits[$unitCode];
      

      $product = Product::updateOrCreate(
        ['external_code' => $item['external_code']],
        [
          'product_type_id' => $typeId,
          'category_id' => $categoryId,
          'catalog_type' => $item['catalog_type'] ?? 'product',
          'unit_id' => $unitId,
          'slug' => $item['slug'],
          'name' => $item['name'],
          'is_active' => true,
        ]
      );

      $this->attachMedia($product, $item['preview_picture'] ?? null, $item['detail_picture'] ?? null, $command);
      $this->saveEav($product, $item['eav'] ?? []);

      foreach ($item['variants'] ?? [] as $vData) {
        $variant = ProductVariant::updateOrCreate(
          ['external_code' => $vData['external_code']],
          [
            'product_id' => $product->id,
            'sku' => $vData['sku'],
            'cost_price' => $vData['cost_price'] ?? 0,
            'is_default' => $vData['is_default'] ?? false,
            'is_active' => true,
          ]
        );

        if (isset($vData['price'])) {
          \Nicole\Box\Core\Models\ProductVariantPrice::updateOrCreate(
            ['product_variant_id' => $variant->id, 'price_type_id' => $retailPriceId],
            ['price' => (float) $vData['price'], 'markup_percent' => 20]
          );
        }

        $this->attachMedia($variant, $vData['preview_picture'] ?? null, $vData['detail_picture'] ?? null, $command);
        $this->saveEav($variant, $vData['eav'] ?? []);

        $stockQty = (float) ($vData['stock'] ?? 0);
        if ($stockQty > 0) {
          \Nicole\Box\Core\Models\Stock::updateOrCreate(
            ['product_variant_id' => $variant->id, 'warehouse_id' => $mainWarehouse->id],
            ['quantity' => $stockQty, 'reserved' => 0]
          );
        }
      }

      $product->refreshMinPrice();
      $bar->advance();
    }

    $bar->finish();
    $command->line('');
  }

  /**
   * Преобразует EAV (где ключи и значения это строки external_code) во внутренние ID базы и сохраняет.
   */
  private function saveEav($model, array $eavData): void
  {
    
    ProductAttributeValue::where('attributable_id', $model->id)
      ->where('attributable_type', $model->getMorphClass())
      ->delete();

    foreach ($eavData as $attrCode => $valueOrValues) {
      /** @var Attribute $attribute */
      $attribute = $this->mapAttributes[$attrCode] ?? null;
      if (!$attribute) {
        continue;
      }

      
      $values = is_array($valueOrValues) ? $valueOrValues : [$valueOrValues];

      foreach ($values as $value) {
        if ($value === null || $value === '') {
          continue;
        }

        $recordData = [
          'attribute_id' => $attribute->id,
          'attributable_id' => $model->id,
          'attributable_type' => $model->getMorphClass(),
          'value_string' => null,
          'value_numeric' => null,
          'value_boolean' => null,
          'value_option_id' => null,
          'value_complex_id' => null,
        ];

        // Маппинг значений согласно типу атрибута
        if ($attribute->type === Attribute::TYPE_DICTIONARY) {
          $recordData['value_option_id'] = $this->mapOptions[$value] ?? null;
        } elseif ($attribute->type === Attribute::TYPE_COMPLEX) {
          $recordData['value_complex_id'] = $this->mapComplexRecords[$value] ?? null;
        } elseif ($attribute->type === Attribute::TYPE_BOOLEAN) {
          $recordData['value_boolean'] = (bool) $value;
        } elseif ($attribute->type === Attribute::TYPE_NUMERIC) {
          $recordData['value_numeric'] = (float) $value;
        } else {
          $recordData['value_string'] = (string) $value;
        }

        // Создаем запись только если хоть какое-то значение заполнено
        if (array_filter(array_slice($recordData, 3)) !== []) {
          ProductAttributeValue::create($recordData);
        }
      }
    }
  }

  /**
   * Интеллектуальная привязка изображений
   */
  private function attachMedia($model, ?string $previewPath, ?string $detailPath, Command $command): void
  {
    $baseDir = base_path('import/export_images/');

    // 1. Прикрепляем превью
    if ($previewPath) {
      $fullPath = $baseDir . ltrim($previewPath, '/');
      if (File::exists($fullPath)) {
        $existingMedia = $model->getFirstMedia('preview');
        $fileName = basename($fullPath);

        if (!$existingMedia || $existingMedia->file_name !== $fileName) {
          $model->clearMediaCollection('preview');
          $model->addMedia($fullPath)
            ->preservingOriginal()
            ->withCustomProperties(['skip_conversions' => true])
            ->toMediaCollection('preview');
        }
      } else {
        $command->warn("\n⚠ Товар/SKU {$model->external_code}: Превью не найдено -> {$fullPath}");
      }
    }

    // 2. Прикрепляем основное фото (main)
    if ($detailPath) {
      $fullPath = $baseDir . ltrim($detailPath, '/');
      if (File::exists($fullPath)) {
        $existingMedia = $model->getFirstMedia('main');
        $fileName = basename($fullPath);

        if (!$existingMedia || $existingMedia->file_name !== $fileName) {
          $model->clearMediaCollection('main');
          $media = $model->addMedia($fullPath)->preservingOriginal();

          
          
          if ($previewPath) {
            $media->withCustomProperties(['skip_conversions' => true]);
          }

          $media->toMediaCollection('main');
        }
      } else {
        $command->warn("\n⚠ Товар/SKU {$model->external_code}: Основное фото не найдено -> {$fullPath}");
      }
    }
  }

  
  private function warmUpCache(): void
  {
    $this->mapTypes = ProductType::pluck('id', 'external_code')->toArray();
    $this->mapCategories = Category::pluck('id', 'external_code')->toArray();
    $this->mapOptions = AttributeOption::pluck('id', 'external_code')->toArray();
    $this->mapComplexRecords = ComplexDictionaryRecord::pluck('id', 'external_code')->toArray();

    
    $this->mapUnits = Unit::pluck('id', 'slug')->toArray();

    
    $this->mapAttributes = Attribute::all()->keyBy('code')->all();
  }
}
