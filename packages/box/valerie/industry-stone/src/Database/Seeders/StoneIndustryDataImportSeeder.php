<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nicole\Box\Core\Models\Attribute;
use Nicole\Box\Core\Models\AttributeOption;
use Nicole\Box\Core\Models\Category;
use Nicole\Box\Core\Models\ComplexDictionary;
use Nicole\Box\Core\Models\ComplexDictionaryRecord;
use Nicole\Box\Core\Models\SettingSchema;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductAttributeValue;
use Nicole\Box\Core\Models\ProductFamily;
use Nicole\Box\Core\Models\ProductType;
use Nicole\Box\Core\Models\ProductVariant;
use Nicole\Box\Core\Models\ProductVariantPrice;
use Nicole\Box\Core\Models\Stock;
use Nicole\Box\Core\Models\Unit;
use Nicole\Box\Core\Models\Warehouse;

class StoneIndustryDataImportSeeder extends Seeder
{
    private array $variantOnlyMap = [];

    private array $attrMap = [];

    private array $optionsMap = [];

    private array $complexRecordsMap = [];

    private ?int $retailPriceTypeId = null;

    private ?int $mainWarehouseId = null;

    private array $defaultChannelSettings = [
        'channels' => [
            'widget' => ['is_public' => true],
            'catalog' => ['is_public' => true],
        ],
    ];

    private const array TRANSFORM_RULES = [
        'bend_akril' => ['is_bend', Attribute::TYPE_BOOLEAN],
        'price_categories' => ['price_group', Attribute::TYPE_COMPLEX],
        'color_ref' => ['color', Attribute::TYPE_DICTIONARY],
        'collection_stone_ref' => ['collection', Attribute::TYPE_DICTIONARY],
        'min_sale_part_stone' => null,
        'min_product_size_stone' => null,
        'direction_division_stone' => null,
        'is_pattern_repeat_stone' => null,
        'is_separate_cutting_stone' => null,
        'product_type' => null,
        'type_stone' => null,
        'use_in_calc' => null,
    ];

    public function run(): void
    {
        Cache::flush();
        $this->command->info('0. Seeding dictionaries schemas...');
        $this->call(StoneIndustryDictionariesSeeder::class);

        $jsonPath = base_path('import/import_ready_filtered.json');
        if (! File::exists($jsonPath)) {
            return;
        }
        $data = json_decode(File::get($jsonPath), true);

        $this->retailPriceTypeId = PriceType::where('slug', 'retail')->value('id');
        $this->mainWarehouseId = Warehouse::firstOrCreate(['slug' => 'main'], [
            'name' => ['ru' => 'Главный склад', 'en' => 'Main Warehouse'],
            'is_active' => true,
            'settings' => $this->defaultChannelSettings,
        ])->id;

        $this->command->info('1. Metadata transformation...');
        $this->transformMetadata($data);

        $this->command->info('2. Importing Families and Types...');
        $this->importFamiliesAndTypes($data);

        $this->command->info('3. Importing Categories...');
        $this->importCategories($data);

        $this->command->info('4. Importing Complex Dictionaries...');
        $this->importComplexRecords($data);

        $this->command->info('5. Importing Attributes Metadata...');
        $this->importAttributes($data);

        $this->command->info('6. Attaching Attributes to 8 Product Types...');
        $this->attachAttributesToProductTypes();

        $this->command->info('7. Importing Products...');
        $this->warmUpCache();
        $this->importProducts($data);

        $this->command->info('8. Seeding ALL Metadata Schemas...');
        $this->seedSettingSchemas();

        $this->command->info('Catalog import completed successfully!');
    }

    private function transformMetadata(array &$data): void
    {
        if (isset($data['attributes'])) {
            $data['attributes'] = collect($data['attributes'])
                ->reject(fn ($attr) => array_key_exists($attr['code'], self::TRANSFORM_RULES) && self::TRANSFORM_RULES[$attr['code']] === null)
                ->map(function ($attr) {
                    if (isset(self::TRANSFORM_RULES[$attr['code']])) {
                        [$attr['code'], $attr['type']] = self::TRANSFORM_RULES[$attr['code']];
                    }

                    return $attr;
                })->toArray();
        }

        if (isset($data['dictionaries'])) {
            foreach (self::TRANSFORM_RULES as $old => $cfg) {
                if ($cfg === null) {
                    unset($data['dictionaries'][$old]);

                    continue;
                }
                if ($old !== $cfg[0] && isset($data['dictionaries'][$old])) {
                    $data['dictionaries'][$cfg[0]] = $data['dictionaries'][$old];
                    unset($data['dictionaries'][$old]);
                }
            }
        }
    }

    private function importFamiliesAndTypes(array $data): void
    {
        foreach ($data['product_families'] ?? [] as $family) {
            ProductFamily::updateOrCreate(
                ['code' => $family['code']],
                ['name' => $family['name'], 'is_active' => true, 'settings' => $this->defaultChannelSettings]
            );
        }

        foreach ($data['product_types'] ?? [] as $type) {
            $familyId = ProductFamily::where('code', $type['family_code'])->value('id');
            $settings = $this->defaultChannelSettings;

            if (in_array($type['code'], ['acrylic_stone', 'quartz_stone'])) {
                $isAcrylic = $type['code'] === 'acrylic_stone';
                $settings['channels']['widget'] = array_merge($settings['channels']['widget'], [
                    'step' => $isAcrylic ? 0.5 : 1.0,
                    'minPart' => $isAcrylic ? 12 : 20,
                    'maxStack' => 1,
                    'axisX' => $isAcrylic,
                ]);
                $settings['pricing_mode'] = 'complex_dictionary';
                $settings['pricing_attr_code'] = 'price_group';
                $settings['pricing_field_name'] = 'material_cost';
                $settings['pricing_currency'] = 'USD';
            }

            ProductType::updateOrCreate(['code' => $type['code']], [
                'family_id' => $familyId,
                'name' => $type['name'],
                'settings' => $settings,
                'is_active' => true,
            ]);
        }
    }

    private function importCategories(array $data): void
    {
        $catMap = [];
        $accId = Category::updateOrCreate(['slug' => 'accessories'], [
            'name' => ['ru' => 'Комплектующие и бортики'],
            'settings' => $this->defaultChannelSettings,
        ])->id;

        foreach (collect($data['categories'] ?? [])->sortBy('depth') as $cat) {
            $parentId = $cat['parent_id'] ? ($catMap[$cat['parent_id']] ?? null) : null;
            if (in_array((int) $cat['id'], [21, 22]) && ! $parentId) {
                $parentId = $accId;
            }

            $slug = ! empty($cat['slug']) ? $cat['slug'] : Str::slug($cat['name']['ru'].'-'.$cat['id']);
            $model = Category::updateOrCreate(['external_code' => (string) $cat['id']], [
                'name' => $cat['name'], 'slug' => $slug, 'parent_id' => $parentId, 'settings' => $this->defaultChannelSettings,
            ]);
            $catMap[$cat['id']] = $model->id;
        }
    }

    private function importComplexRecords(array $data): void
    {
        foreach ($data['complex_dictionaries'] ?? [] as $code => $dict) {
            $effCode = isset(self::TRANSFORM_RULES[$code]) ? self::TRANSFORM_RULES[$code][0] : $code;
            $dictionary = ComplexDictionary::where('code', $effCode)->first();
            if (! $dictionary) {
                continue;
            }

            foreach ($dict['records'] ?? [] as $index => $rec) {
                $oldPayload = $rec['payload'] ?? [];
                $oldRef = (string) ($rec['slug'] ?? $rec['id'] ?? '');

                if ($effCode === 'price_group') {
                    $newPayload = ['material_cost' => (float) ($oldPayload['cost_price'] ?? 0), 'material_cost_markup' => 15];
                } elseif ($effCode === 'cutting_groups') {
                    $newPayload = ['rotate' => (bool) ($oldPayload['rotate'] ?? false), 'cut' => (bool) ($oldPayload['cut'] ?? false)];
                } else {
                    $newPayload = $oldPayload;
                }
                $newPayload['_old_ref'] = $oldRef;

                ComplexDictionaryRecord::updateOrCreate(
                    ['dictionary_id' => $dictionary->id, 'external_code' => $oldRef],
                    ['name' => $rec['name'], 'slug' => Str::slug($oldRef ?: "rec-{$index}"), 'payload' => $newPayload, 'is_active' => true]
                );
            }
        }
    }

  private function importAttributes(array $data): void
  {
    $extra = [
      ['code' => 'length', 'type' => Attribute::TYPE_NUMERIC, 'name' => ['ru' => 'Длина', 'en' => 'Length']],
      ['code' => 'width', 'type' => Attribute::TYPE_NUMERIC, 'name' => ['ru' => 'Ширина', 'en' => 'Width']],
      ['code' => 'height', 'type' => Attribute::TYPE_NUMERIC, 'name' => ['ru' => 'Толщина', 'en' => 'Thickness']],
      ['code' => 'price_group', 'type' => Attribute::TYPE_COMPLEX, 'name' => ['ru' => 'Ценовая группа', 'en' => 'Price group']],
      ['code' => 'cutting_groups', 'type' => Attribute::TYPE_COMPLEX, 'name' => ['ru' => 'Группа раскроя', 'en' => 'Cutting group']],
      ['code' => 'target_material', 'type' => Attribute::TYPE_DICTIONARY, 'name' => ['ru' => 'Для материала', 'en' => 'For material']],
      ['code' => 'size_inner_sink', 'type' => Attribute::TYPE_STRING, 'name' => ['ru' => 'Размер чаши', 'en' => 'Inner bowl size']],
    ];

    foreach (array_merge($data['attributes'] ?? [], $extra) as $attrData) {
      $code = isset(self::TRANSFORM_RULES[$attrData['code']]) ? self::TRANSFORM_RULES[$attrData['code']][0] : $attrData['code'];

      // 1. Формируем настройки канала для самого Атрибута
      $attrSettings = $this->defaultChannelSettings;
      $attrSettings['channels']['widget']['is_filterable'] = true;
      $attrSettings['channels']['widget']['is_collapsed'] = false;

      // Если это атрибут "Цвет", ставим визуальный тип "color" (кружочки)
      if ($code === 'color') {
        $attrSettings['channels']['widget']['filter_type'] = 'color';
        $attrSettings['channels']['catalog']['filter_type'] = 'color';
      } else {
        $attrSettings['channels']['widget']['filter_type'] = 'checkbox';
        $attrSettings['channels']['catalog']['filter_type'] = 'select';
      }

      $attribute = Attribute::updateOrCreate(['code' => $code], [
        'name' => $attrData['name'],
        'type' => $attrData['type'] ?? Attribute::TYPE_DICTIONARY,
        'complex_dictionary_id' => ($attrData['type'] ?? '') === Attribute::TYPE_COMPLEX ? ComplexDictionary::where('code', $code)->value('id') : null,
        'settings' => $attrSettings,
        'is_active' => true,
      ]);

      // 2. Импорт опций (значений) справочника
      if ($attribute->type === Attribute::TYPE_DICTIONARY && isset($data['dictionaries'][$attrData['code']])) {
        foreach ($data['dictionaries'][$attrData['code']] as $idx => $opt) {

          // Извлекаем HEX: из специального поля или из слага
          $hex = $opt['icon_hex'] ?? null;
          if (! $hex && str_starts_with((string) $opt['slug'], '#')) {
            $hex = $opt['slug'];
          }

          AttributeOption::updateOrCreate(
            ['attribute_id' => $attribute->id, 'slug' => $opt['slug']],
            [
              'value' => $opt['value'],
              'sort_order' => $idx * 10,
              'settings' => [
                // Помещаем HEX в блок visual для FilterResource
                'visual' => [
                  'hex' => $hex,
                  'icon' => null,
                ],
                // Настройки видимости самой опции в каналах
                'channels' => [
                  'widget'  => ['is_public' => true],
                  'catalog' => ['is_public' => true],
                ],
              ],
            ]
          );
        }
      }
    }
  }

    private function attachAttributesToProductTypes(): void
    {
        $mapping = [
            'acrylic_stone' => ['color' => true, 'collection' => false, 'texture' => false, 'is_bend' => false, 'effect_akril' => false, 'inclusions_akril' => false, 'marketing_tags' => false, 'price_group' => false, 'cutting_groups' => false, 'length' => false, 'width' => false, 'height' => false],
            'quartz_stone' => ['color' => true, 'collection' => false, 'texture' => false, 'polishing_quartz' => false, 'marketing_tags' => false, 'price_group' => false, 'cutting_groups' => false, 'length' => false, 'width' => false, 'height' => false],
            'kitchen_sink' => ['color' => true, 'brand' => false, 'material' => false, 'size_inner_sink' => false, 'min_cab_width' => false, 'steel_thickness_sink' => false],
            'bathroom_sink' => ['color' => true, 'brand' => false, 'material' => false, 'size_inner_sink' => false],
            'faucet' => ['color' => true, 'brand' => false, 'features_faucet' => false, 'type_faucet' => false],
            'dispenser' => ['color' => true, 'brand' => false, 'type_faucet' => false],
            'edge' => ['color' => true, 'length' => false, 'width' => false, 'height' => false],
            'skirting' => ['color' => true, 'length' => false, 'width' => false, 'height' => false],
        ];

        foreach ($mapping as $typeCode => $attrs) {
            $type = ProductType::where('code', $typeCode)->first();
            if (! $type) {
                continue;
            }
            $sync = [];
            $s = 10;
            foreach ($attrs as $c => $vo) {
                $a = Attribute::where('code', $c)->first();
                if ($a) {
                    $sync[$a->id] = ['sort_order' => $s += 10, 'is_variant_only' => (bool) $vo, 'is_required' => false];
                }
            }
            $type->attributes()->sync($sync);
        }
    }

    private function warmUpCache(): void
    {
        $this->attrMap = Attribute::pluck('type', 'code')->toArray();
        $this->optionsMap = AttributeOption::all()->groupBy('attribute_id')->map(fn ($i) => $i->pluck('id', 'slug')->toArray())->toArray();
        foreach (ComplexDictionaryRecord::with('dictionary')->get() as $r) {
            if (isset($r->payload['_old_ref'])) {
                $this->complexRecordsMap[$r->dictionary->code][(string) $r->payload['_old_ref']] = $r->id;
            }
        }
        $this->variantOnlyMap = DB::table('attribute_product_type')
            ->join('attributes', 'attributes.id', '=', 'attribute_product_type.attribute_id')
            ->where('is_variant_only', true)
            ->select('product_type_id', 'attributes.code')
            ->get()
            ->groupBy('product_type_id')
            ->map(fn ($v) => $v->pluck('code')->toArray())
            ->toArray();
    }

    private function importProducts(array $data): void
    {
        $items = $data['items'] ?? [];
        $total = count($items);
        $pcsId = Unit::where('slug', 'pcs')->value('id') ?? 1;
        $mId = Unit::where('slug', 'm')->value('id') ?? 1;

        foreach ($items as $index => $item) {
            $rawEav = $item['eav'] ?? [];
            $typeCode = $item['product_type_code'] ?? 'acrylic_stone';

            if ($typeCode === 'item') {
                if (($rawEav['type_stone'] ?? '') === 'akril') {
                    $typeCode = 'acrylic_stone';
                } elseif (($rawEav['type_stone'] ?? '') === 'quartz') {
                    $typeCode = 'quartz_stone';
                } elseif (($rawEav['product_type'] ?? '') === 'kitchen_sink_type') {
                    $typeCode = 'kitchen_sink';
                }
            }

            $type = ProductType::where('code', $typeCode)->first();
            $typeId = $type?->id ?? 1;

            $product = Product::updateOrCreate(['external_code' => (string) $item['id']], [
                'catalog_type' => 'product',
                'product_type_id' => $typeId,
                'category_id' => Category::where('external_code', (string) $item['category_id'])->value('id'),
                'unit_id' => in_array($typeCode, ['edge', 'skirting']) ? $mId : $pcsId,
                'name' => $item['name'],
                'slug' => strtolower($item['slug']),
                'is_active' => true,
                'settings' => $this->defaultChannelSettings,
            ]);

            // Разделение EAV на Товар и SKU
            $voCodes = $this->variantOnlyMap[$typeId] ?? [];
            $prodEav = [];
            $varEav = [];

            if (! empty($item['price_category_slug'])) {
                $rawEav['price_group'] = $item['price_category_slug'];
            }
            if (! empty($item['cutting_group_id'])) {
                $rawEav['cutting_groups'] = (string) $item['cutting_group_id'];
            }

            foreach ($rawEav as $k => $v) {
                $c = isset(self::TRANSFORM_RULES[$k]) ? self::TRANSFORM_RULES[$k][0] : $k;
                if (in_array($c, $voCodes)) {
                    $varEav[$k] = $v;
                } else {
                    $prodEav[$k] = $v;
                }
            }

            $this->attachEav($product, $prodEav);

            $variants = empty($item['variants'])
              ? [['id' => $item['id'].'_def', 'slug' => $item['slug'].'-def', 'price' => $item['price'] ?? 0, 'eav' => $varEav]]
              : $item['variants'];

            foreach ($variants as $v) {
                $price = (float) ($v['price'] ?? 0);
                $variant = ProductVariant::updateOrCreate(['external_code' => (string) $v['id']], [
                    'product_id' => $product->id, 'sku' => $v['slug'], 'cost_price' => round($price / 1.15, 2),
                    'currency' => 'RUB', 'is_default' => true, 'is_active' => true, 'settings' => $this->defaultChannelSettings,
                ]);

                $this->attachEav($variant, array_merge($varEav, $v['eav'] ?? []));

                if ($this->retailPriceTypeId) {
                    ProductVariantPrice::updateOrCreate(['product_variant_id' => $variant->id, 'price_type_id' => $this->retailPriceTypeId], ['price' => $price, 'markup_percent' => 15]);
                }
                Stock::updateOrCreate(['product_variant_id' => $variant->id, 'warehouse_id' => $this->mainWarehouseId], ['quantity' => 10]);
            }

            $product->refreshMinPrice();
            if (($index + 1) % 50 === 0 || ($index + 1) === $total) {
                $this->command->info('   - Processed '.($index + 1)."/{$total} products...");
            }
        }
    }

    private function attachEav($model, array $eavData): void
    {
        foreach ($eavData as $rawCode => $val) {
            $code = isset(self::TRANSFORM_RULES[$rawCode]) ? self::TRANSFORM_RULES[$rawCode][0] : $rawCode;
            $type = $this->attrMap[$code] ?? null;
            if (! $type || blank($val)) {
                continue;
            }

            $attrId = Attribute::where('code', $code)->value('id');
            $data = ['value_string' => null, 'value_numeric' => null, 'value_boolean' => null, 'value_option_id' => null, 'value_complex_id' => null];

            if ($type === Attribute::TYPE_BOOLEAN) {
                $data['value_boolean'] = in_array(mb_strtolower((string) $val), ['да', '1', 'true', 'yes'], true);
            } elseif ($type === Attribute::TYPE_NUMERIC) {
                $data['value_numeric'] = (float) $val;
            } elseif ($type === Attribute::TYPE_DICTIONARY) {
                $data['value_option_id'] = $this->optionsMap[$attrId][strtolower((string) $val)] ?? null;
            } elseif ($type === Attribute::TYPE_COMPLEX) {
                $data['value_complex_id'] = $this->complexRecordsMap[$code][(string) $val] ?? null;
            } else {
                $data['value_string'] = (string) $val;
            }

            ProductAttributeValue::updateOrCreate(['attribute_id' => $attrId, 'attributable_id' => $model->id, 'attributable_type' => $model->getMorphClass(), ], $data);
        }
    }

    private function seedSettingSchemas(): void
    {
        // 1. Атрибуты (Фильтры)
        SettingSchema::updateOrCreate(['entity_type' => 'attribute'], [
            'schema' => [
                ['key' => 'is_filterable', 'type' => 'boolean', 'label' => ['ru' => 'Использовать как фильтр', 'en' => 'Use as filter'], 'width' => 1],
                ['key' => 'is_collapsed', 'type' => 'boolean', 'label' => ['ru' => 'Свернуть по умолчанию', 'en' => 'Collapsed by default'], 'width' => 1],
                ['key' => 'filter_type', 'type' => 'select', 'label' => ['ru' => 'Вид фильтра', 'en' => 'Filter UI type'], 'options' => ['checkbox' => 'Список чекбоксов', 'select' => 'Выпадающий список', 'color' => 'Цветовые кружки', 'range' => 'Диапазон (слайдер)'], 'width' => 2],
            ],
        ]);

        // 2. Типы товаров (Камень)
        SettingSchema::updateOrCreate(['entity_type' => 'product_type'], [
            'schema' => [
                ['key' => 'step', 'type' => 'number', 'label' => ['ru' => 'Шаг размера', 'en' => 'Size step'], 'width' => 1],
                ['key' => 'minPart', 'type' => 'number', 'label' => ['ru' => 'Минимальная часть', 'en' => 'Min part'], 'width' => 1],
                ['key' => 'maxStack', 'type' => 'number', 'label' => ['ru' => 'Макс. стопка', 'en' => 'Max stack'], 'width' => 1],
                ['key' => 'axisX', 'type' => 'boolean', 'label' => ['ru' => 'По оси X', 'en' => 'Along X axis'], 'width' => 1],
            ],
        ]);

        // 3. Товары
        SettingSchema::updateOrCreate(['entity_type' => 'product'], [
            'schema' => [
                ['key' => 'badge_text', 'type' => 'text', 'label' => ['ru' => 'Текст на бейдже', 'en' => 'Badge text'], 'width' => 1],
                ['key' => 'is_featured', 'type' => 'boolean', 'label' => ['ru' => 'Рекомендуемый товар', 'en' => 'Is featured'], 'width' => 1],
            ],
        ]);

        // 4. Семейства
        SettingSchema::updateOrCreate(['entity_type' => 'family'], [
            'schema' => [
                ['key' => 'show_in_menu', 'type' => 'boolean', 'label' => ['ru' => 'Показывать в меню', 'en' => 'Show in menu'], 'width' => 1],
            ],
        ]);

        // 5. Категории
        SettingSchema::updateOrCreate(['entity_type' => 'category'], [
            'schema' => [
                ['key' => 'is_promoted', 'type' => 'boolean', 'label' => ['ru' => 'Продвигаемая категория', 'en' => 'Is promoted'], 'width' => 1],
            ],
        ]);

        // 6. Умные справочники
        SettingSchema::updateOrCreate(['entity_type' => 'complex_dictionary'], [
            'schema' => [
                ['key' => 'contains_prices', 'type' => 'boolean', 'label' => ['ru' => 'Содержит финансовые данные', 'en' => 'Contains price data'], 'width' => 1],
            ],
        ]);

        // 7. Типы цен
        SettingSchema::updateOrCreate(['entity_type' => 'price_type'], [
            'schema' => [
                ['key' => 'api_visible', 'type' => 'boolean', 'label' => ['ru' => 'Доступно в API', 'en' => 'Visible in API'], 'width' => 1],
            ],
        ]);

        // 8. Валюты
        SettingSchema::updateOrCreate(['entity_type' => 'currency'], [
            'schema' => [
                ['key' => 'decimal_places', 'type' => 'number', 'label' => ['ru' => 'Знаков после запятой', 'en' => 'Decimal places'], 'width' => 1],
            ],
        ]);
    }
}
