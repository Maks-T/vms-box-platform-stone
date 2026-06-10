<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Tests\Feature\Filament;

use Tests\TestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use App\Models\User;
use Nicole\Box\Core\Models\Role;
use Nicole\Box\Core\Models\Unit;
use Nicole\Box\Core\Filament\Resources\Units\Pages\ListUnits;
use Nicole\Box\Core\Filament\Resources\Units\Pages\CreateUnit;
use Nicole\Box\Core\Filament\Resources\Units\Pages\EditUnit;
use Livewire\Livewire;

class UnitResourceTest extends TestCase
{
  use LazilyRefreshDatabase; // Очищаем базу данных перед тестом

  protected User $adminUser;

  protected function setUp(): void
  {
    parent::setUp();

    // Сбрасываем кэш ролей Spatie
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    // Создаем администратора панели
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole($adminRole);
  }

  /**
   * Сценарий: Проверка отображения списка единиц измерения в таблице.
   */
  public function test_can_render_list_units_page(): void
  {
    $this->actingAs($this->adminUser);

    // Создаем тестовую единицу измерения
    $unit = Unit::factory()->create(['slug' => 'm2']);

    Livewire::test(ListUnits::class)
      ->assertSuccessful()
      ->assertCanSeeTableRecords([$unit]); // Проверяем отображение в таблице
  }

  /**
   * Сценарий: Создание новой единицы измерения через форму Filament.
   */
  public function test_can_create_unit_via_form(): void
  {
    $this->actingAs($this->adminUser);

    Livewire::test(CreateUnit::class)
      ->fillForm([
        'name' => [
          'ru' => 'Литр',
          'en' => 'Liter',
        ],
        'slug' => 'liter',
        'symbol' => [
          'ru' => 'л',
          'en' => 'l',
        ],
        'code' => '112',
      ])
      ->call('create') // Имитируем сохранение формы
      ->assertHasNoFormErrors();

    // Проверяем наличие записи в базе данных
    $this->assertDatabaseHas('units', [
      'slug' => 'liter',
      'code' => '112',
    ]);
  }

  /**
   * Сценарий: Проверка валидации полей (название и символ обязательны).
   */
  public function test_unit_fields_are_validated(): void
  {
    $this->actingAs($this->adminUser);

    Livewire::test(CreateUnit::class)
      ->fillForm([
        'name' => [], // Пустое переводимое поле
        'slug' => 'test-unit',
        'symbol' => [], // Пустое переводимое поле
      ])
      ->call('create')
      ->assertHasFormErrors([
        'name' => 'required',
        'symbol' => 'required',
      ]);
  }

  /**
   * Сценарий: Редактирование существующей единицы измерения.
   */
  public function test_can_edit_unit_via_form(): void
  {
    $this->actingAs($this->adminUser);

    $unit = Unit::factory()->create([
      'slug' => 'm3',
      'code' => '113',
    ]);

    // Инициализируем страницу редактирования для записи
    Livewire::test(EditUnit::class, [
      'record' => $unit->getKey(),
    ])
      ->fillForm([
        'code' => '114', // Обновляем код стандарта
      ])
      ->call('save')
      ->assertHasNoFormErrors();

    // Проверяем, что значение обновилось
    $this->assertEquals('114', $unit->refresh()->code);
  }
}
