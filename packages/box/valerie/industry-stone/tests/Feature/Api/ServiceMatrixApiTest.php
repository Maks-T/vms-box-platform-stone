<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Nicole\Box\Core\Models\Channel;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductVariant;
use Nicole\Box\Core\Models\ProductVariantPrice;
use Nicole\Box\Core\Models\Attribute;
use Nicole\Box\Core\Models\AttributeOption;
use Nicole\Box\Core\Models\ProductAttributeValue;

class ServiceMatrixApiTest extends TestCase
{
  use LazilyRefreshDatabase; // Быстрая миграция БД в изолированной транзакции [1]

  /** @var \Nicole\Box\Core\Models\PriceType */
  protected $retailPriceType;

  /** @var \Nicole\Box\Core\Models\Attribute */
  protected $targetMaterialAttribute;

  /** @var \Nicole\Box\Core\Models\AttributeOption */
  protected $optAcrylic;

  /** @var \Nicole\Box\Core\Models\AttributeOption */
  protected $optQuartz;

  protected function setUp(): void
  {
    parent::setUp();

    // Создаем канал для прохождения Middleware
    Channel::create([
      'code' => 'widget',
      'name' => ['ru' => 'Виджет', 'en' => 'Widget'],
      'is_active' => true,
    ]);

    // Создаем базовую валюту системы
    $rub = Currency::factory()->create([
      'code' => 'RUB',
      'rate' => 1.0,
      'is_default' => true,
    ]);

    // Создаем тип цен по умолчанию
    $this->retailPriceType = PriceType::factory()->create([
      'slug' => 'retail',
      'is_default' => true,
      'currency_id' => $rub->id,
    ]);

    // Создаем EAV-атрибут "target_material" (целевой материал)
    $this->targetMaterialAttribute = Attribute::factory()->create([
      'code' => 'target_material',
      'type' => Attribute::TYPE_DICTIONARY,
    ]);

    // Создаем справочные значения материалов (Акрил и Кварц)
    $this->optAcrylic = AttributeOption::factory()->create([
      'attribute_id' => $this->targetMaterialAttribute->id,
      'slug' => 'acrylic_stone',
      'value' => ['ru' => 'Акриловый камень', 'en' => 'Acrylic stone'],
    ]);

    $this->optQuartz = AttributeOption::factory()->create([
      'attribute_id' => $this->targetMaterialAttribute->id,
      'slug' => 'quartz_stone',
      'value' => ['ru' => 'Кварцевый агломерат', 'en' => 'Quartz stone'],
    ]);
  }

  /**
   * Сценарий: Проверка сборки плоской матрицы цен на услуги вырезов для Акрила и Кварца.
   */
  public function test_services_matrix_endpoint_returns_success_and_correct_prices(): void
  {
    // Создаем услугу "Вырез под накладную мойку" (catalog_type = service)
    $service = Product::factory()->service()->create([
      'slug' => 'cutout_top',
      'name' => ['ru' => 'Вырез под накладную мойку', 'en' => 'Top-mount cutout'],
    ]);

    // Создаем вариант услуги для Акрила с ценой 1650
    $variantAcrylic = ProductVariant::factory()->create([
      'product_id' => $service->id,
      'sku' => 'cutout_top_acrylic',
      'is_active' => true,
      'cost_price' => 1650.0, // Для услуг закупка = розница
    ]);
    ProductVariantPrice::factory()->create([
      'product_variant_id' => $variantAcrylic->id,
      'price_type_id' => $this->retailPriceType->id,
      'markup_percent' => 0.0, // маржа 0%
    ]);
    ProductAttributeValue::factory()->create([
      'attribute_id' => $this->targetMaterialAttribute->id,
      'attributable_id' => $variantAcrylic->id,
      'attributable_type' => $variantAcrylic->getMorphClass(),
      'value_option_id' => $this->optAcrylic->id,
    ]);

    // Создаем вариант услуги для Кварца с ценой 2500
    $variantQuartz = ProductVariant::factory()->create([
      'product_id' => $service->id,
      'sku' => 'cutout_top_quartz',
      'is_active' => true,
      'cost_price' => 2500.0, // Для услуг закупка = розница
    ]);
    ProductVariantPrice::factory()->create([
      'product_variant_id' => $variantQuartz->id,
      'price_type_id' => $this->retailPriceType->id,
      'markup_percent' => 0.0, // маржа 0%
    ]);
    ProductAttributeValue::factory()->create([
      'attribute_id' => $this->targetMaterialAttribute->id,
      'attributable_id' => $variantQuartz->id,
      'attributable_type' => $variantQuartz->getMorphClass(),
      'value_option_id' => $this->optQuartz->id,
    ]);

    // Выполняем GET-запрос к API матрицы цен индустрии камня
    $response = $this->withHeaders([
      'X-Sales-Channel' => 'widget',
      'Accept-Language' => 'ru',
    ])->getJson('/api/v1/stone/services-matrix');

    // Ожидаем успешный статус 200
    $response->assertStatus(200);

    // Проверяем полную JSON-структуру ответа
    $response->assertJsonStructure([
      'status',
      'data' => [
        'services' => [
          '*' => [
            'id',
            'slug',
            'name',
            'unit',
            'prices' => [
              'acrylic_stone',
              'quartz_stone',
            ],
          ],
        ],
      ],
    ]);

    // Проверяем, что матрица цен для выреза cutout_top рассчиталась верно
    $response->assertJsonFragment([
      'slug' => 'cutout_top',
      'name' => 'Вырез под накладную мойку',
      'prices' => [
        'acrylic_stone' => 1650.0,
        'quartz_stone' => 2500.0,
      ],
    ]);
  }
}
