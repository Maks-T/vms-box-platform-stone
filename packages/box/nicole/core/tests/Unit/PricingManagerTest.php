<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Tests\Unit;

use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductVariant;
use Tests\TestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Services\PricingManager;

class PricingManagerTest extends TestCase
{
  use LazilyRefreshDatabase;

  // Автоматически мигрирует тестовую БД `vms_stone_db_test` и изолирует тесты [1].

  protected PricingManager $pricingManager;

  protected function setUp(): void
  {
    parent::setUp();

    $this->pricingManager = app(PricingManager::class);
  }

  /**
   * Сценарий 1: Конвертация из иностранной валюты в базовую.
   */
  public function test_it_converts_foreign_currency_to_base_currency(): void
  {
    // Создаем базовую валюту (Рубли) [2]
    Currency::factory()->create([
      'code' => 'RUB',
      'rate' => 1.0,
      'is_default' => true,
    ]);

    // Создаем иностранную валюту (Доллары, курс 95.5) [2]
    Currency::factory()->create([
      'code' => 'USD',
      'rate' => 95.5,
      'is_default' => false,
    ]);

    // Конвертируем 10 USD в RUB (10 * 95.5 = 955.0) [2]
    $result = $this->pricingManager->convert(10.0, 'USD', 'RUB');

    $this->assertEquals(955.0, $result);
  }

  /**
   * Сценарий 2: Конвертация из базовой валюты в иностранную.
   */
  public function test_it_converts_base_currency_to_foreign_currency(): void
  {
    Currency::factory()->create([
      'code' => 'RUB',
      'rate' => 1.0,
      'is_default' => true,
    ]);

    Currency::factory()->create([
      'code' => 'USD',
      'rate' => 95.5,
      'is_default' => false,
    ]);

    // Конвертируем 955 RUB в USD (955 / 95.5 = 10.0) [2]
    $result = $this->pricingManager->convert(955.0, 'RUB', 'USD');

    $this->assertEquals(10.0, $result);
  }

  /**
   * Сценарий 3: Сложная кросс-конвертация двух иностранных валют.
   */
  public function test_it_converts_between_two_foreign_currencies(): void
  {
    Currency::factory()->create([
      'code' => 'RUB',
      'rate' => 1.0,
      'is_default' => true,
    ]);

    Currency::factory()->create([
      'code' => 'USD',
      'rate' => 100.0, // Условный курс 100 рублей за доллар
      'is_default' => false,
    ]);

    Currency::factory()->create([
      'code' => 'EUR',
      'rate' => 110.0, // Условный курс 110 рублей за евро
      'is_default' => false,
    ]);

    // Конвертируем 110 USD в EUR:
    // 110 USD * 100 (курс USD) = 11000 рублей -> 11000 / 110 (курс EUR) = 100 EUR [2]
    $result = $this->pricingManager->convert(110.0, 'USD', 'EUR');

    $this->assertEquals(100.0, $result);
  }

  /**
   * Сценарий 4: Тестирование динамического расчета цены SKU на основе Умного Справочника.
   */
  public function test_it_calculates_price_from_complex_dictionary(): void
  {
    // Создаем валюты (Базовая RUB и валюта закупки USD)
    $rub = Currency::factory()->create([
      'code' => 'RUB',
      'rate' => 1.0,
      'is_default' => true,
    ]);

    $usd = Currency::factory()->create([
      'code' => 'USD',
      'rate' => 100.0, // Для удобства счета: 100 рублей за доллар
      'is_default' => false,
    ]);

    // Создаем дефолтный тип цен
    $retailPriceType = \Nicole\Box\Core\Models\PriceType::factory()->create([
      'slug' => 'retail',
      'is_default' => true,
      'currency_id' => $rub->id,
    ]);

    // Создаем Умный Справочник (Ценовые группы) со схемой полей
    $complexDictionary = \Nicole\Box\Core\Models\ComplexDictionary::factory()->create([
      'code' => 'price_group',
      'meta_schema' => [
        [
          'key' => 'material_cost',
          'type' => 'price',
          'currency' => 'USD', // Валюта закупки - доллары
          'is_public' => true,
        ],
      ],
    ]);

    // Создаем конкретную запись в справочнике (Категория M0)
    // Закупка: 100 USD, наценка: 15%
    $complexRecord = \Nicole\Box\Core\Models\ComplexDictionaryRecord::factory()->create([
      'dictionary_id' => $complexDictionary->id,
      'slug' => 'm0',
      'meta' => [
        'material_cost' => 100.0,
        'material_cost_markup' => 15.0, // Наценка 15%
      ],
    ]);

    // Создаем EAV-атрибут типа "complex_reference" (ссылка на умный справочник)
    $attribute = \Nicole\Box\Core\Models\Attribute::factory()->create([
      'code' => 'price_group',
      'type' => \Nicole\Box\Core\Models\Attribute::TYPE_COMPLEX,
      'complex_dictionary_id' => $complexDictionary->id,
    ]);

    // Создаем тип товара с режимом ценообразования через справочник
    $productType = \Nicole\Box\Core\Models\ProductType::factory()->create([
      'pricing_mode' => 'complex_dictionary',
      'pricing_attribute_id' => $attribute->id,
      'pricing_field' => 'material_cost',
    ]);

    // Создаем товар и привязываем к нему значение EAV (запись M0 из справочника)
    $product = Product::factory()->create([
      'product_type_id' => $productType->id,
    ]);

    \Nicole\Box\Core\Models\ProductAttributeValue::factory()->create([
      'attribute_id' => $attribute->id,
      'attributable_id' => $product->id,
      'attributable_type' => $product->getMorphClass(),
      'value_complex_id' => $complexRecord->id, // Привязали запись M0
    ]);

    // Создаем модификацию (SKU) для этого товара
    $variant = ProductVariant::factory()->create([
      'product_id' => $product->id,
      'cost_price' => 0.0, // Ручная цена не задана
    ]);

    // Запускаем расчет цены модификации через PricingManager
    // Ожидаемый расчет:
    // 100 USD (закупка) * 100 (курс USD к RUB) = 10 000 рублей.
    // 10 000 рублей + 15% (наценка) = 11 500 рублей.
    $calculatedPrice = $this->pricingManager->getVariantPrice($variant);

    $this->assertEquals(11500.0, $calculatedPrice);
  }

}
