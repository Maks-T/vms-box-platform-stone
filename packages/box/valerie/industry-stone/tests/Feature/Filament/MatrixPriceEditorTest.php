<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Tests\Feature\Filament;

use Tests\TestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use App\Models\User;
use Nicole\Box\Core\Models\Role;
use Nicole\Box\Core\Models\Channel;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductVariant;
use Nicole\Box\Core\Models\ProductVariantPrice;
use Nicole\Box\Core\Models\Attribute;
use Nicole\Box\Core\Models\AttributeOption;
use Nicole\Box\Core\Models\ProductAttributeValue;
use Valerie\Box\IndustryStone\Filament\Pages\MatrixPriceEditor;
use Livewire\Livewire;
use Nicole\Box\Core\Services\PricingManager;

class MatrixPriceEditorTest extends TestCase
{
  use LazilyRefreshDatabase; // Быстрая и изолированная миграция тестовой БД

  /** @var \App\Models\User */
  protected $adminUser;

  /** @var \Nicole\Box\Core\Models\PriceType */
  protected $retailPriceType;

  /** @var \Nicole\Box\Core\Models\Attribute */
  protected $targetMaterialAttribute;

  /** @var \Nicole\Box\Core\Models\AttributeOption */
  protected $optAcrylic;

  /** @var \Nicole\Box\Core\Models\Product */
  protected $service;

  /** @var \Nicole\Box\Core\Models\ProductVariant */
  protected $variantAcrylic;

  protected function setUp(): void
  {
    parent::setUp();

    // Сбрасываем кэш Spatie
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    // Создаем пользователя-администратора
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole($adminRole);

    // Создаем каналы и валюту
    Channel::create([
      'code' => 'widget',
      'name' => ['ru' => 'Виджет'],
      'is_active' => true,
    ]);

    $rub = Currency::factory()->create(['code' => 'RUB', 'rate' => 1.0, 'is_default' => true]);

    $this->retailPriceType = PriceType::factory()->create([
      'slug' => 'retail',
      'is_default' => true,
      'currency_id' => $rub->id,
    ]);

    // Создаем EAV-атрибут материала "target_material"
    $this->targetMaterialAttribute = Attribute::factory()->create([
      'code' => 'target_material',
      'type' => Attribute::TYPE_DICTIONARY,
    ]);

    $this->optAcrylic = AttributeOption::factory()->create([
      'attribute_id' => $this->targetMaterialAttribute->id,
      'slug' => 'acrylic_stone',
      'value' => ['ru' => 'Акриловый камень'],
    ]);

    // Создаем услугу обработки
    $this->service = Product::factory()->service()->create([
      'slug' => 'cutout_top',
      'name' => ['ru' => 'Вырез под накладную мойку'],
    ]);

    // Создаем вариант услуги для Акрила (дефолтная цена 1650)
    $this->variantAcrylic = ProductVariant::factory()->create([
      'product_id' => $this->service->id,
      'sku' => 'cutout_top_acrylic',
      'is_active' => true,
      'cost_price' => 1650.0, // Услуга
    ]);

    ProductVariantPrice::factory()->create([
      'product_variant_id' => $this->variantAcrylic->id,
      'price_type_id' => $this->retailPriceType->id,
      'markup_percent' => 0.0, // 0% маржа
    ]);

    ProductAttributeValue::factory()->create([
      'attribute_id' => $this->targetMaterialAttribute->id,
      'attributable_id' => $this->variantAcrylic->id,
      'attributable_type' => $this->variantAcrylic->getMorphClass(),
      'value_option_id' => $this->optAcrylic->id,
    ]);
  }

  /**
   * Сценарий 1: Администратор может открыть страницу матрицы цен
   */
  public function test_admin_can_render_matrix_price_editor_page(): void
  {
    $this->actingAs($this->adminUser);

    Livewire::test(MatrixPriceEditor::class)
      ->assertSuccessful();
  }

  /**
   * Сценарий 2: Таблица отображает загруженные услуги и их записи
   */
  public function test_matrix_table_renders_service_records(): void
  {
    $this->actingAs($this->adminUser);

    Livewire::test(MatrixPriceEditor::class)
      ->assertCanSeeTableRecords([$this->service]);
  }

  /**
   * Сценарий 3: Редактирование ячейки цены через таблицу Filament физически обновляет цену в БД
   */
  public function test_updating_table_column_state_updates_database_price(): void
  {
    $this->actingAs($this->adminUser);

    // Вызываем метод обновления значения в ячейке таблицы
    Livewire::test(MatrixPriceEditor::class)
      ->call(
        'updateTableColumnState',
        'mat_acrylic_stone',
        (string) $this->service->id,
        '1750'
      );

    // Освежаем модель в памяти, чтобы загрузить обновленные в БД cost_price и is_manual_pricing
    $this->variantAcrylic->refresh();

    // Проверяем, что на лету рассчитанная цена продажи через PricingManager равна ровно 1750.00
    $calculatedPrice = app(PricingManager::class)->getVariantPrice($this->variantAcrylic, 'retail');

    $this->assertEquals(1750.0, $calculatedPrice);
  }

}
