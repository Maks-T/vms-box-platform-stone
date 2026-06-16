<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Nicole\Box\Core\Models\Currency;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Unit;
use Nicole\Box\Core\Models\OrderStatus;

class CommerceInfrastructureSeeder extends Seeder
{
  public function run(): void
  {
    // Базовая настройка видимости для каналов
    $channelSettings = [
      'channels' => [
        'widget' => ['is_public' => true],
        'catalog' => ['is_public' => true],
      ],
    ];

    /**
     * 1. Единицы измерения
     */
    Unit::updateOrCreate(
      ['slug' => 'pcs'],
      [
        'code' => '796',
        'name' => ['ru' => 'Штука', 'en' => 'Piece'],
        'symbol' => ['ru' => 'шт.', 'en' => 'pcs'],
      ],
    );

    Unit::updateOrCreate(
      ['slug' => 'srv'],
      [
        'code' => 'E48',
        'name' => ['ru' => 'Услуга', 'en' => 'Service'],
        'symbol' => ['ru' => 'усл.', 'en' => 'srv'],
      ],
    );

    /**
     * 2. Валюты
     */
    $rub = Currency::updateOrCreate(
      ['code' => 'RUB'],
      [
        'symbol' => '₽',
        'name' => ['ru' => 'Российский рубль', 'en' => 'Russian Ruble'],
        'rate' => 1.0,
        'is_default' => true,
        'is_active' => true,
        'settings' => $channelSettings,
      ],
    );

    Currency::updateOrCreate(
      ['code' => 'USD'],
      [
        'symbol' => '$',
        'name' => ['ru' => 'Доллар США', 'en' => 'US Dollar'],
        'rate' => 95.5,
        'is_default' => false,
        'is_active' => true,
        'settings' => $channelSettings,
      ],
    );

    Currency::updateOrCreate(
      ['code' => 'BYN'],
      [
        'symbol' => 'Br',
        'name' => ['ru' => 'Белорусский рубль', 'en' => 'Belarusian Ruble'],
        'rate' => 29.5,
        'is_default' => false,
        'is_active' => true,
        'settings' => $channelSettings,
      ],
    );

    /**
     * 3. Типы цен
     */
    PriceType::updateOrCreate(
      ['slug' => 'retail'],
      [
        'name' => ['ru' => 'Цена продажи', 'en' => 'Retail'],
        'is_default' => true,
        'currency_id' => $rub->id,
        'settings' => $channelSettings,
      ],
    );

    /**
     * 4. Динамические статусы заказов
     */
    $statuses = [
      [
        'slug' => 'draft',
        'name' => ['ru' => 'Черновик', 'en' => 'Draft'],
        'color' => 'gray',
        'is_default' => true,
        'is_active' => true,
        'sort_order' => 10,
      ],
      [
        'slug' => 'new',
        'name' => ['ru' => 'Новый расчет', 'en' => 'New Quote'],
        'color' => 'info',
        'is_default' => false,
        'is_active' => true,
        'sort_order' => 20,
      ],
      [
        'slug' => 'confirmed',
        'name' => ['ru' => 'Подтвержден менеджером', 'en' => 'Confirmed'],
        'color' => 'success',
        'is_default' => false,
        'is_active' => true,
        'sort_order' => 30,
      ],
      [
        'slug' => 'manufacturing',
        'name' => ['ru' => 'В производстве', 'en' => 'In Production'],
        'color' => 'warning',
        'is_default' => false,
        'is_active' => true,
        'sort_order' => 40,
      ],
      [
        'slug' => 'completed',
        'name' => ['ru' => 'Завершен', 'en' => 'Completed'],
        'color' => 'primary',
        'is_default' => false,
        'is_active' => true,
        'sort_order' => 50,
      ],
    ];

    foreach ($statuses as $status) {
      OrderStatus::updateOrCreate(
        ['slug' => $status['slug']],
        $status
      );
    }

    $this->command->info(
      'Core: Infrastructure (Units, Currencies, PriceTypes, OrderStatuses) seeded successfully.'
    );
  }
}
