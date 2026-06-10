<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Tests\Feature\Filament;

use Tests\TestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use App\Models\User;
use Nicole\Box\Core\Models\Role;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Filament\Resources\Currencies\Pages\ListCurrencies;
use Nicole\Box\Core\Filament\Resources\Currencies\Pages\CreateCurrency;
use Nicole\Box\Core\Filament\Resources\Currencies\Pages\EditCurrency;
use Livewire\Livewire;

class CurrencyResourceTest extends TestCase
{
  use LazilyRefreshDatabase; // Изолирует базу данных для теста

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
   * Сценарий: Проверка отображения списка валют в таблице Filament.
   */
  public function test_can_render_list_currencies_page(): void
  {
    $this->actingAs($this->adminUser);

    // Создаем тестовую валюту
    $currency = Currency::factory()->create(['code' => 'USD']);

    Livewire::test(ListCurrencies::class)
      ->assertSuccessful()
      ->assertCanSeeTableRecords([$currency]); // Проверяем, что видим ее в таблице
  }

  /**
   * Сценарий: Создание новой валюты через форму.
   */
  public function test_can_create_currency_via_form(): void
  {
    $this->actingAs($this->adminUser);

    // Заполняем форму, используя точечную нотацию для переводимых полей
    Livewire::test(CreateCurrency::class)
      ->fillForm([
        'name.ru' => 'Евро',
        'name.en' => 'Euro',
        'code' => 'EUR',
        'symbol.ru' => '€',
        'symbol.en' => '€',
        'rate' => 105.0,
        'is_active' => true,
      ])
      ->call('create') // Имитируем нажатие кнопки "Создать"
      ->assertHasNoFormErrors();

    // Проверяем, что запись физически появилась в БД
    $this->assertDatabaseHas('currencies', [
      'code' => 'EUR',
      'rate' => 105.0,
    ]);
  }

  /**
   * Сценарий: Проверка валидации полей (код валюты обязателен).
   */
  public function test_currency_code_is_required_to_create(): void
  {
    $this->actingAs($this->adminUser);

    Livewire::test(CreateCurrency::class)
      ->fillForm([
        'name.ru' => 'Без кода',
        'code' => '', // Оставляем пустым
      ])
      ->call('create')
      ->assertHasFormErrors(['code' => 'required']); // Ожидаем ошибку валидации
  }

  /**
   * Сценарий: Редактирование существующей валюты.
   */
  public function test_can_edit_currency_via_form(): void
  {
    $this->actingAs($this->adminUser);

    // Создаем дефолтную валюту (чтобы не нарушать уникальность)
    Currency::factory()->create(['code' => 'RUB', 'is_default' => true]);

    $currency = Currency::factory()->create([
      'code' => 'USD',
      'rate' => 95.0,
      'is_default' => false,
    ]);

    // Инициализируем страницу редактирования для конкретной записи
    Livewire::test(EditCurrency::class, [
      'record' => $currency->getKey(),
    ])
      ->fillForm([
        'rate' => 98.5, // Меняем курс
      ])
      ->call('save')
      ->assertHasNoFormErrors();

    // Проверяем изменение значения в памяти модели
    $this->assertEquals(98.5, $currency->refresh()->rate);
  }

}
