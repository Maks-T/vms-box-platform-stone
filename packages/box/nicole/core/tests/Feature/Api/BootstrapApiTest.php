<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Nicole\Box\Core\Models\Channel;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\ProductFamily;
use Nicole\Box\Core\Models\ProductType;
use Nicole\Box\Core\Models\ComplexDictionary;
use Nicole\Box\Core\Models\ComplexDictionaryRecord;

class BootstrapApiTest extends TestCase
{
  use LazilyRefreshDatabase; 

  protected function setUp(): void
  {
    parent::setUp();

    // Создаем обязательный контекст канала продаж
    Channel::create([
      'code' => 'widget',
      'name' => ['ru' => 'Виджет калькулятора', 'en' => 'Calculator Widget'],
      'is_active' => true,
    ]);

    // Создаем базовую валюту (Рубли) и валюту закупки (Доллары)
    Currency::factory()->create([
      'code' => 'RUB',
      'rate' => 1.0,
      'symbol' => '₽',
      'is_default' => true,
    ]);

    Currency::factory()->create([
      'code' => 'USD',
      'rate' => 100.0, // Для удобства: 100 руб. за доллар
      'is_default' => false,
    ]);

    // Создаем дефолтный тип цен с описанием для покрытия ветки description
    PriceType::factory()->create([
      'slug' => 'retail',
      'name' => ['ru' => 'Розничная цена', 'en' => 'Retail'],
      'description' => ['ru' => 'Основной прайс-лист', 'en' => 'Main price list'],
      'is_default' => true,
    ]);

    // Создаем Семейство товаров с активной схемой и настройками видимости в меню
    $family = ProductFamily::factory()->create([
      'code' => 'stone',
      'meta_schema' => [
        [
          'key' => 'max_slabs',
          'type' => 'number',
          'label' => [
            'ru' => 'Максимум плит в пачке',
            'en' => 'Max Slabs',
          ],
        ]
      ],
      'settings' => [
        'channels' => [
          'widget' => [
            'is_public' => true,
            'is_settings_public' => true,
            'show_in_menu' => true, // Проходим условие фильтрации меню
          ]
        ]
      ]
    ]);

    // Создаем Тип товара, привязанный к семейству
    ProductType::factory()->create([
      'family_id' => $family->id,
      'code' => 'acrylic_stone',
      'meta' => [
        'custom_key' => 'custom_value',
      ],
      'settings' => [
        'channels' => [
          'widget' => [
            'is_public' => true,
            'is_settings_public' => true,
          ]
        ]
      ]
    ]);

    // Создаем Умный Справочник с ценовым полем [2]
    $complexDictionary = ComplexDictionary::factory()->create([
      'code' => 'price_group',
      'meta_schema' => [
        [
          'key' => 'purchase_cost',
          'type' => 'price', // Покрываем ветку FIELD_TYPE_PRICE [2]
          'currency' => 'USD',
          'is_public' => true,
          'label' => [
            'ru' => 'Закупка',
            'en' => 'Purchase',
          ],
        ]
      ],
      'settings' => [
        'channels' => [
          'widget' => [
            'is_public' => true,
            'is_settings_public' => true,
          ]
        ]
      ]
    ]);

    // Создаем запись умного справочника (Закупка 100 USD, наценка 15%) [2]
    ComplexDictionaryRecord::factory()->create([
      'dictionary_id' => $complexDictionary->id,
      'slug' => 'm0',
      'meta' => [
        'purchase_cost' => 100.0,
        'purchase_cost_markup' => 15.0,
      ],
    ]);
  }

  /**
   * Сценарий: Проверка полной структуры инициализации канала.
   */
  public function test_bootstrap_endpoint_returns_success_and_correct_structure(): void
  {
    $response = $this->withHeaders([
      'X-Sales-Channel' => 'widget',
      'Accept-Language' => 'ru',
    ])->getJson('/api/v1/bootstrap');

    $response->assertStatus(200);

    // Проверяем полную JSON-структуру ответа [2]
    $response->assertJsonStructure([
      'status',
      'data' => [
        'base_currency' => [
          'code',
          'symbol',
        ],
        'price_types',
        'dictionaries' => [
          '*' => [
            'code',
            'name',
            'schema',
            'records' => [
              '*' => [
                'id',
                'slug',
                'name',
                'meta' => [
                  'purchase_cost_total', // Должно автоматически посчитаться в RUB
                ],
              ],
            ],
          ],
        ],
        'families' => [
          '*' => [
            'code',
            'name',
            'schema',
            'types' => [
              '*' => [
                'code',
                'name',
                'meta',
              ],
            ],
          ],
        ],
      ],
    ]);

    $response->assertJsonPath('status', 'success');
    $response->assertJsonPath('data.base_currency.code', 'RUB');

    // Извлекаем рассчитанную цену из JSON-ответа
    $calculatedPrice = $response->json('data.dictionaries.0.records.0.meta.purchase_cost_total');

    // Проверяем, что это числовое значение
    $this->assertIsNumeric($calculatedPrice);

    // Проверяем его значение через assertEquals (нестрогое сравнение int vs float)
    $this->assertEquals(11500.0, $calculatedPrice);
  }

  /**
   * Сценарий: Блокировка запросов от некорректного или отсутствующего канала продаж.
   */
  public function test_bootstrap_endpoint_requires_valid_channel(): void
  {
    $response = $this->withHeaders([
      'X-Sales-Channel' => 'unknown_channel',
    ])->getJson('/api/v1/bootstrap');

    $response->assertStatus(403);
    $response->assertJsonFragment([
      'error' => 'Invalid or inactive sales channel',
    ]);
  }

}