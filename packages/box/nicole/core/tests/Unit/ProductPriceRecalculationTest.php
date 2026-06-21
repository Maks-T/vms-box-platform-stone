<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductVariant;
use Nicole\Box\Core\Models\ProductVariantPrice;

class ProductPriceRecalculationTest extends TestCase
{
  use LazilyRefreshDatabase;

  // Автоматически очищает базу данных между тестами

  /** @var \Nicole\Box\Core\Models\PriceType */
  protected PriceType|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model $retailPriceType;

  protected function setUp(): void
  {
    parent::setUp();

    // Создаем базовую валюту (Рубли)
    $rub = Currency::factory()->create([
      'code' => 'RUB',
      'rate' => 1.0,
      'is_default' => true,
    ]);

    // Создаем дефолтный тип цен
    $this->retailPriceType = PriceType::factory()->create([
      'slug' => 'retail',
      'is_default' => true,
      'currency_id' => $rub->id,
    ]);
  }

  /**
   * Сценарий 1: Автоматическое обновление min_price при создании первой цены.
   */
  public function test_product_min_price_updates_when_variant_price_is_created(): void
  {
    $product = Product::factory()->create(['min_price' => 0.0]);
    $this->assertEquals(0.0, $product->min_price);

    $variant = ProductVariant::factory()->create([
      'product_id' => $product->id,
      'is_active' => true,
      'cost_price' => 4000.0, // Задаем себестоимость 4000
    ]);

    // Наценка 25% от 4000 дает цену 5000.00
    ProductVariantPrice::factory()->create([
      'product_variant_id' => $variant->id,
      'price_type_id' => $this->retailPriceType->id,
      'markup_percent' => 25.0,
    ]);

    $product->refresh();
    $this->assertEquals(5000.0, $product->min_price);
  }

  /**
   * Сценарий 2: Выбор минимальной цены среди нескольких вариантов и автопересчет при отключении одного из них.
   */
  public function test_product_min_price_takes_the_lowest_active_variant_price(): void
  {
    $product = Product::factory()->create(['min_price' => 0.0]);

    // Дорогой вариант (закупка 10000 + наценка 20% = 12000.00)
    $variant1 = ProductVariant::factory()->create([
      'product_id' => $product->id,
      'is_active' => true,
      'cost_price' => 10000.0,
    ]);
    ProductVariantPrice::factory()->create([
      'product_variant_id' => $variant1->id,
      'price_type_id' => $this->retailPriceType->id,
      'markup_percent' => 20.0,
    ]);

    // Дешевый вариант (закупка 10000 + наценка -15% = 8500.00)
    $variant2 = ProductVariant::factory()->create([
      'product_id' => $product->id,
      'is_active' => true,
      'cost_price' => 10000.0,
    ]);
    ProductVariantPrice::factory()->create([
      'product_variant_id' => $variant2->id,
      'price_type_id' => $this->retailPriceType->id,
      'markup_percent' => -15.0,
    ]);

    $product->refresh();
    $this->assertEquals(8500.0, $product->min_price);

    $variant2->update(['is_active' => false]);

    $product->refresh();
    $this->assertEquals(12000.0, $product->min_price);
  }

}
