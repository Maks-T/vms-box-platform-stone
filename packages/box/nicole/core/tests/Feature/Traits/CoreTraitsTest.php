<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Tests\Feature\Traits;

use Tests\TestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Nicole\Box\Core\Models\Channel;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductVariant;

class CoreTraitsTest extends TestCase
{
  use LazilyRefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    // Подменяем публичный диск на изолированное виртуальное хранилище в памяти
    Storage::fake('public');

    // Создаем обязательный канал продаж
    Channel::create([
      'code' => 'widget',
      'name' => ['ru' => 'Виджет калькулятора'],
      'is_active' => true,
    ]);

    // Создаем базовую валюту (Рубли) для работы калькулятора цен
    $rub = Currency::factory()->create([
      'code' => 'RUB',
      'rate' => 1.0,
      'is_default' => true,
    ]);

    // Создаем базовый тип цен по умолчанию
    PriceType::factory()->create([
      'slug' => 'retail',
      'is_default' => true,
      'currency_id' => $rub->id,
    ]);
  }

  /**
   * Сценарий: Тестирование получения настроек каналов продаж в HasSettings.
   */
  public function test_has_settings_trait_helper_methods(): void
  {
    $product = Product::factory()->create();

    // Проверяем вызов геттера настроек конкретного канала
    $channelSettings = $product->getChannelSettings('widget');
    $this->assertIsArray($channelSettings);
    $this->assertTrue($channelSettings['is_public']);

    // Проверяем вызов метода проверки активности в канале
    $isEnabled = $product->isEnabledInChannel('widget');
    $this->assertTrue($isEnabled);
  }

  /**
   * Сценарий: Тестирование работы с медиафайлами, конвертаций и ссылок-превью в HasNicoleMedia.
   */
  public function test_has_nicole_media_trait_flow(): void
  {
    // Создаем базовый товар
    /** @var Product $product */
    $product = Product::factory()->create();

    // Проверяем, что у товара без картинок превью возвращает null
    $this->assertNull($product->getPreviewUrl());

    // Имитируем загрузку и прикрепление основного изображения (в коллекцию main)
    $product->addMedia(UploadedFile::fake()->image('granite.jpg'))
      ->toMediaCollection('main');

    // Освежаем модель в памяти, чтобы сбросить кэш отношений
    $product->refresh();

    // Проверяем, что getPreviewUrl() находит конвертированное превью
    $previewUrl = $product->getPreviewUrl();
    $this->assertNotNull($previewUrl);

    // Проверяем наличие ключевых директорий и имени конвертации в пути
    $this->assertStringContainsString('conversions', $previewUrl);
    $this->assertStringContainsString('preview', $previewUrl);

    // Имитируем ручную загрузку отдельного готового превью (в коллекцию preview)
    $product->addMedia(UploadedFile::fake()->image('granite_thumb.jpg'))
      ->toMediaCollection('preview');

    // Освежаем модель в памяти еще раз
    $product->refresh();

    // Проверяем, что getPreviewUrl() отдает именно это превью в приоритете
    $customPreviewUrl = $product->getPreviewUrl();
    $this->assertNotNull($customPreviewUrl);
    $this->assertStringContainsString('preview/granite_thumb', $customPreviewUrl);

    // Проверяем каскадный поиск превью для модификации (SKU)
    /** @var ProductVariant $variant */
    $variant = ProductVariant::factory()->create([
      'product_id' => $product->id,
    ]);

    // Модификация товара должна автоматически подхватить превью родительского товара
    $variantPreviewUrl = $variant->getPreviewUrl();
    $this->assertNotNull($variantPreviewUrl);
    $this->assertEquals($customPreviewUrl, $variantPreviewUrl);
  }

}
