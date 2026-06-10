<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Nicole\Box\Core\Models\Channel;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Models\PriceType;

class BootstrapApiTest extends TestCase
{
  use LazilyRefreshDatabase; // Очищает базу данных перед тестом в изолированной транзакции

  protected function setUp(): void
  {
    parent::setUp();

    // Создаем обязательный для прохождения Middleware канал продаж
    Channel::create([
      'code' => 'widget',
      'name' => ['ru' => 'Виджет калькулятора', 'en' => 'Calculator Widget'],
      'is_active' => true,
    ]);

    // Создаем базовую валюту системы
    Currency::factory()->create([
      'code' => 'RUB',
      'rate' => 1.0,
      'symbol' => '₽',
      'is_default' => true,
    ]);

    // Создаем дефолтный тип цен
    PriceType::factory()->create([
      'slug' => 'retail',
      'name' => ['ru' => 'Розничная цена', 'en' => 'Retail'],
      'is_default' => true,
    ]);
  }

  /**
   * Сценарий 1: Проверка успешной отдачи структуры данных при корректном канале.
   */
  public function test_bootstrap_endpoint_returns_success_and_correct_structure(): void
  {
    // Выполняем GET-запрос с передачей обязательного заголовка контекста канала
    $response = $this->withHeaders([
      'X-Sales-Channel' => 'widget',
      'Accept-Language' => 'ru',
    ])->getJson('/api/v1/bootstrap');

    // Ожидаем успешный HTTP статус 200
    $response->assertStatus(200);

    // Проверяем полную структуру JSON-ответа
    $response->assertJsonStructure([
      'status',
      'data' => [
        'base_currency' => [
          'code',
          'symbol',
        ],
        'price_types',
        'dictionaries',
        'families',
      ],
    ]);

    // Проверяем конкретные значения в ответе
    $response->assertJsonPath('status', 'success');
    $response->assertJsonPath('data.base_currency.code', 'RUB');
    $response->assertJsonPath('data.base_currency.symbol', '₽');
  }

  /**
   * Сценарий 2: Защита эндпоинта от некорректного или отсутствующего канала продаж.
   */
  public function test_bootstrap_endpoint_requires_valid_channel(): void
  {
    // Выполняем запрос с несуществующим каналом
    $response = $this->withHeaders([
      'X-Sales-Channel' => 'unknown_channel',
    ])->getJson('/api/v1/bootstrap');

    // Должна вернуться ошибка доступа 403 Forbidden
    $response->assertStatus(403);
    $response->assertJsonFragment([
      'error' => 'Invalid or inactive sales channel',
    ]);
  }

}
