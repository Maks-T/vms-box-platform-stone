<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Nicole\Box\Core\Models\Attribute;
use Nicole\Box\Core\Models\AttributeOption;
use Nicole\Box\Core\Models\Category;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductAttributeValue;
use Nicole\Box\Core\Models\ProductFamily;
use Nicole\Box\Core\Models\ProductType;
use Nicole\Box\Core\Models\ProductVariant;
use Nicole\Box\Core\Models\ProductVariantPrice;
use Nicole\Box\Core\Models\Unit;

class StoneIndustryServicesSeeder extends Seeder
{
  private array $importConfig = [
    'categories' => [
      'cutouts' => [
        'name' => ['ru' => 'Вырезы и отверстия', 'en' => 'Cutouts & Holes'],
      ],
      'surface_sinks' => [
        'name' => [
          'ru' => 'Интеграция моек и поверхности',
          'en' => 'Sinks & Surface',
        ],
      ],
      'logistics' => [
        'name' => [
          'ru' => 'Замер и Доставка',
          'en' => 'Measurement & Delivery',
        ],
      ],
      'installation' => [
        'name' => [
          'ru' => 'Монтаж и Демонтаж',
          'en' => 'Installation & Dismantling',
        ],
      ],
    ],

    'services' => [
      [
        'slug' => 'cutout_top',
        'category' => 'cutouts',
        'unit' => 'pcs',
        'image' => null,
        'name' => [
          'ru' => 'Вырез под накладную мойку / варочную панель',
          'en' => 'Cutout for top-mount sink / hob',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => false,
        ],
        'prices' => ['acrylic_stone' => 1650, 'quartz_stone' => 2500],
      ],
      [
        'slug' => 'cutout_under',
        'category' => 'cutouts',
        'unit' => 'pcs',
        'image' => null,
        'name' => [
          'ru' => 'Вырез под подстольную мойку (с полировкой)',
          'en' => 'Cutout for under-mount sink (polished)',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => false,
        ],
        'prices' => ['acrylic_stone' => 2500, 'quartz_stone' => 4500],
      ],
      [
        'slug' => 'hole_faucet',
        'category' => 'cutouts',
        'unit' => 'pcs',
        'image' => null,
        'name' => [
          'ru' => 'Отверстие под смеситель / дозатор / фильтр',
          'en' => 'Hole for faucet / dispenser / filter',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => false,
        ],
        'prices' => ['acrylic_stone' => 300, 'quartz_stone' => 500],
      ],
      [
        'slug' => 'hole_socket',
        'category' => 'cutouts',
        'unit' => 'pcs',
        'image' => null,
        'name' => ['ru' => 'Вырез под розетку', 'en' => 'Cutout for socket'],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => true,
        ],
        'prices' => ['acrylic_stone' => 400, 'quartz_stone' => 800],
      ],
      [
        'slug' => 'hole_radiator',
        'category' => 'cutouts',
        'unit' => 'pcs',
        'image' => null,
        'name' => [
          'ru' => 'Прорези под конвектор / радиатор (шт.)',
          'en' => 'Convection slots for radiator (pcs)',
        ],
        'applies_to' => [
          'kitchen' => false,
          'bathroom' => false,
          'windowsill' => true,
        ],
        'prices' => ['acrylic_stone' => 1250, 'quartz_stone' => 2000],
      ],

      [
        'slug' => 'sink_gluing',
        'category' => 'surface_sinks',
        'unit' => 'srv',
        'image' => null,
        'name' => [
          'ru' => 'Монтаж/вклейка мойки заказчика снизу',
          'en' => 'Under-mount gluing of client sink',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => false,
        ],
        'prices' => ['acrylic_stone' => 4350, 'quartz_stone' => 6000],
      ],
      [
        'slug' => 'sink_make_acrylic',
        'category' => 'surface_sinks',
        'unit' => 'pcs',
        'image' => null,
        'name' => [
          'ru' => 'Изготовление мойки из акрилового камня',
          'en' => 'Making acrylic stone sink',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => false,
        ],
        'prices' => ['acrylic_stone' => 15357],
      ],
      [
        'slug' => 'grooves_drain',
        'category' => 'surface_sinks',
        'unit' => 'set',
        'image' => null,
        'name' => [
          'ru' => 'Проточки (канавки) для слива воды',
          'en' => 'Drain grooves',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => false,
          'windowsill' => false,
        ],
        'prices' => ['acrylic_stone' => 4600, 'quartz_stone' => 7000],
      ],
      [
        'slug' => 'lowering_wing',
        'category' => 'surface_sinks',
        'unit' => 'srv',
        'image' => null,
        'name' => [
          'ru' => 'Обнижение (занижение) крыла мойки',
          'en' => 'Sink wing lowering',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => false,
          'windowsill' => false,
        ],
        'prices' => ['acrylic_stone' => 4000],
      ],

      /** ----------------------------------------------------------------------------------
       * ЗАМЕР И ДОСТАВКА (logistics)
       * ---------------------------------------------------------------------------------- */
      [
        'slug' => 'measure_base',
        'category' => 'logistics',
        'unit' => 'srv',
        'image' => null,
        'name' => [
          'ru' => 'Выезд на замер (в пределах города)',
          'en' => 'Measurement visit (in city limits)',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => true,
        ],
        'prices' => ['acrylic_stone' => 0, 'quartz_stone' => 0],
      ],
      [
        'slug' => 'measure_km',
        'category' => 'logistics',
        'unit' => 'km',
        'image' => null,
        'name' => [
          'ru' => 'Замер: доплата за каждый км от КАД/МКАД',
          'en' => 'Measurement: extra per km outside city',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => true,
        ],
        'prices' => ['acrylic_stone' => 80, 'quartz_stone' => 80],
      ],
      [
        'slug' => 'delivery_base',
        'category' => 'logistics',
        'unit' => 'srv',
        'image' => null,
        'name' => [
          'ru' => 'Базовая доставка (в пределах города)',
          'en' => 'Base delivery (in city limits)',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => true,
        ],
        'prices' => ['acrylic_stone' => 3200, 'quartz_stone' => 5200],
      ],
      [
        'slug' => 'delivery_km',
        'category' => 'logistics',
        'unit' => 'km',
        'image' => null,
        'name' => [
          'ru' => 'Доставка: доплата за каждый км от КАД/МКАД',
          'en' => 'Delivery: extra per km outside city',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => true,
        ],
        'prices' => ['acrylic_stone' => 120, 'quartz_stone' => 120],
      ],

      /** ----------------------------------------------------------------------------------
       * МОНТАЖ И ДЕМОНТАЖ (installation)
       * ---------------------------------------------------------------------------------- */
      [
        'slug' => 'lift_elevator',
        'category' => 'installation',
        'unit' => 'srv',
        'image' => null,
        'name' => [
          'ru' => 'Подъем на грузовом лифте',
          'en' => 'Lifting via freight elevator',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => true,
        ],
        'prices' => ['acrylic_stone' => 0, 'quartz_stone' => 0],
      ],
      [
        'slug' => 'lift_manual_floor',
        'category' => 'installation',
        'unit' => 'floor',
        'image' => null,
        'name' => [
          'ru' => 'Ручной подъем (за каждый этаж)',
          'en' => 'Manual lifting (per floor)',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => true,
        ],
        'prices' => ['acrylic_stone' => 1600, 'quartz_stone' => 2500],
      ],
      [
        'slug' => 'montage_countertop',
        'category' => 'installation',
        'unit' => 'srv',
        'image' => null,
        'name' => [
          'ru' => 'Установка (монтаж) изделия',
          'en' => 'Product installation',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => true,
        ],
        'prices' => ['acrylic_stone' => 5500, 'quartz_stone' => 8000],
      ],
      [
        'slug' => 'dismantling_old',
        'category' => 'installation',
        'unit' => 'srv',
        'image' => null,
        'name' => [
          'ru' => 'Демонтаж старой столешницы',
          'en' => 'Dismantling old countertop',
        ],
        'applies_to' => [
          'kitchen' => true,
          'bathroom' => true,
          'windowsill' => true,
        ],
        'prices' => ['acrylic_stone' => 8000, 'quartz_stone' => 8000],
      ],
    ],
  ];

  private int $retailPriceId;
  private int $serviceTypeId;
  private int $targetAttrId;
  private int $optAcrylicId;
  private int $optQuartzId;
  private array $cachedCategories = [];
  private array $cachedUnits = [];
  private array $targetContextAttrs = [];

  public function run(): void
  {
    $this->command->info('Starting Config-driven Stone Services Import...');

    $this->initBaseData();
    $this->createCategories();
    $this->importServices();

    $this->command->info('Stone Services Seeder completed successfully!');
  }

  private function initBaseData(): void
  {
    $this->retailPriceId = PriceType::where('slug', 'retail')->value('id') ?? 1;

    $family = ProductFamily::firstOrCreate(
      ['code' => 'service'],
      ['name' => ['ru' => 'Услуги', 'en' => 'Services'], 'is_active' => true],
    );

    $serviceType = ProductType::firstOrCreate(
      ['code' => 'processing_service'],
      [
        'family_id' => $family->id,
        'name' => ['ru' => 'Услуги обработки', 'en' => 'Processing Services'],
        'is_active' => true,
      ],
    );
    $this->serviceTypeId = $serviceType->id;

    // 1. Создаем атрибуты-переключатели (Для кухни, ванной, подоконника)
    $contexts = [
      'calc_for_kitchen' => [
        'ru' => 'Для кухни (Калькулятор)',
        'en' => 'For Kitchen',
      ],
      'calc_for_bathroom' => [
        'ru' => 'Для ванной (Калькулятор)',
        'en' => 'For Bathroom',
      ],
      'calc_for_windowsill' => [
        'ru' => 'Для подоконника (Калькулятор)',
        'en' => 'For Windowsill',
      ],
    ];

    $syncData = [];
    $sort = 10;
    foreach ($contexts as $code => $name) {
      $attr = Attribute::firstOrCreate(
        ['code' => $code],
        [
          'type' => Attribute::TYPE_BOOLEAN,
          'name' => $name,
          'is_active' => true,
        ],
      );

      $shortKey = str_replace('calc_for_', '', $code);
      $this->targetContextAttrs[$shortKey] = $attr->id;
      $syncData[$attr->id] = [
        'is_variant_only' => false,
        'sort_order' => $sort++,
      ];
    }

    $serviceType->attributes()->syncWithoutDetaching($syncData);

    // 2. Стандартный атрибут "Для какого камня"
    $targetAttr = Attribute::firstOrCreate(
      ['code' => 'target_material'],
      [
        'type' => Attribute::TYPE_DICTIONARY,
        'name' => ['ru' => 'Для материала'],
      ],
    );
    $this->targetAttrId = $targetAttr->id;
    $this->optAcrylicId = AttributeOption::firstOrCreate(
      ['attribute_id' => $targetAttr->id, 'slug' => 'acrylic_stone'],
      ['value' => ['ru' => 'Акрил']],
    )->id;
    $this->optQuartzId = AttributeOption::firstOrCreate(
      ['attribute_id' => $targetAttr->id, 'slug' => 'quartz_stone'],
      ['value' => ['ru' => 'Кварц']],
    )->id;

    // 3. Единицы измерения
    Unit::firstOrCreate(
      ['slug' => 'km'],
      [
        'code' => '008',
        'name' => ['ru' => 'Километр', 'en' => 'Kilometer'],
        'symbol' => ['ru' => 'км', 'en' => 'km'],
      ],
    );
    Unit::firstOrCreate(
      ['slug' => 'floor'],
      [
        'code' => '000',
        'name' => ['ru' => 'Этаж', 'en' => 'Floor'],
        'symbol' => ['ru' => 'эт.', 'en' => 'fl'],
      ],
    );
    Unit::firstOrCreate(
      ['slug' => 'set'],
      [
        'code' => '839',
        'name' => ['ru' => 'Комплект', 'en' => 'Set'],
        'symbol' => ['ru' => 'компл.', 'en' => 'set'],
      ],
    );

    // Кэшируем ID единиц измерения
    $this->cachedUnits['m'] = Unit::where('slug', 'm')->value('id') ?? 1;
    $this->cachedUnits['pcs'] = Unit::where('slug', 'pcs')->value('id') ?? 1;
    $this->cachedUnits['srv'] = Unit::where('slug', 'srv')->value('id') ?? 1;
    $this->cachedUnits['set'] = Unit::where('slug', 'set')->value('id') ?? 1;
    $this->cachedUnits['km'] = Unit::where('slug', 'km')->value('id') ?? 1;
    $this->cachedUnits['floor'] = Unit::where('slug', 'floor')->value('id') ?? 1;
  }

  private function createCategories(): void
  {
    $rootServiceCat = Category::firstOrCreate(
      ['slug' => 'services'],
      [
        'name' => [
          'ru' => 'Услуги калькулятора',
          'en' => 'Calculator Services',
        ],
        'is_active' => true,
      ],
    );

    foreach ($this->importConfig['categories'] as $slug => $data) {
      $cat = Category::updateOrCreate(
        ['slug' => $slug],
        [
          'name' => $data['name'],
          'parent_id' => $rootServiceCat->id,
          'is_active' => true,
        ],
      );
      $this->cachedCategories[$slug] = $cat->id;
    }
  }

  private function importServices(): void
  {
    $imagesBasePath = base_path('import/services_images');

    foreach ($this->importConfig['services'] as $item) {
      $categoryId = $this->cachedCategories[$item['category']] ?? null;
      $unitId = $this->cachedUnits[$item['unit']] ?? null;

      if (! $categoryId) {
        continue;
      }

      $productData = [
        'catalog_type' => 'service',
        'product_type_id' => $this->serviceTypeId,
        'category_id' => $categoryId,
        'unit_id' => $unitId,
        'name' => $item['name'],
        'is_active' => true,
      ];

      if (isset($item['desc'])) {
        $productData['description'] = $item['desc'];
      }

      $product = Product::updateOrCreate(
        ['slug' => $item['slug']],
        $productData,
      );

      foreach ($item['applies_to'] as $context => $isApplicable) {
        if (isset($this->targetContextAttrs[$context])) {
          ProductAttributeValue::updateOrCreate(
            [
              'attribute_id' => $this->targetContextAttrs[$context],
              'attributable_id' => $product->id,
              'attributable_type' => $product->getMorphClass(), // ИСПРАВЛЕНО!
            ],
            [
              'value_boolean' => $isApplicable,
            ],
          );
        }
      }

      if (! empty($item['image'])) {
        $imagePath = $imagesBasePath.'/'.ltrim($item['image'], '/');

        if (File::exists($imagePath)) {
          if (
            ! $product->hasMedia('main') ||
            $product->getFirstMedia('main')->file_name !==
            basename($item['image'])
          ) {
            $product->clearMediaCollection('main');
            $product
              ->addMedia($imagePath)
              ->preservingOriginal()
              ->withCustomProperties(['skip_conversions' => true])
              ->toMediaCollection('main');
          }
        } else {
          $this->command->warn("Image not found locally: {$imagePath}");
        }
      }

      // Создаем варианты цен (Акрил / Кварц)
      foreach ($item['prices'] as $materialCode => $price) {
        $variant = ProductVariant::updateOrCreate(
          ['sku' => "{$item['slug']}_{$materialCode}"],
          [
            'product_id' => $product->id,
            'cost_price' => $price > 0 ? $price * 0.8 : 0,
            'currency' => 'RUB',
            'is_default' => false,
          ],
        );

        ProductVariantPrice::updateOrCreate(
          [
            'product_variant_id' => $variant->id,
            'price_type_id' => $this->retailPriceId,
          ],
          ['markup_percent' => 20, 'price' => (float) $price],
        );

        ProductAttributeValue::updateOrCreate(
          [
            'attribute_id' => $this->targetAttrId,
            'attributable_id' => $variant->id,
            'attributable_type' => $variant->getMorphClass(), // ИСПРАВЛЕНО!
          ],
          [
            'value_option_id' => $materialCode === 'acrylic_stone'
              ? $this->optAcrylicId
              : $this->optQuartzId,
          ],
        );
      }

      $product->refreshMinPrice();
    }
  }
}
