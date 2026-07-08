<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Nicole\Box\Core\Models\Unit;

class CommerceInfrastructureSeeder extends Seeder
{
  public function run(): void
  {
    /**
     * Стандартные единицы измерения (ОКЕИ / UN CEFACT)
     */
    $units = [
      [
        'slug' => 'pcs',
        'code' => '796', // Штука (ОКЕИ: 796, UN CEFACT: PCE/C62)
        'name' => ['ru' => 'Штука', 'en' => 'Piece'],
        'symbol' => ['ru' => 'шт.', 'en' => 'pcs'],
      ],
      [
        'slug' => 'srv',
        'code' => 'E48', // Услуга (UN CEFACT: E48)
        'name' => ['ru' => 'Услуга', 'en' => 'Service'],
        'symbol' => ['ru' => 'усл.', 'en' => 'srv'],
      ],
      [
        'slug' => 'm2',
        'code' => '055', // Квадратный метр (ОКЕИ: 055, UN CEFACT: MTK)
        'name' => ['ru' => 'Квадратный метр', 'en' => 'Square Meter'],
        'symbol' => ['ru' => 'м²', 'en' => 'm²'],
      ],
      [
        'slug' => 'm',
        'code' => '018', // Погонный метр (ОКЕИ: 018, UN CEFACT: LM)
        'name' => ['ru' => 'Погонный метр', 'en' => 'Linear Meter'],
        'symbol' => ['ru' => 'м.п.', 'en' => 'lm'],
      ],
      [
        'slug' => 'set',
        'code' => '671', // Комплект (ОКЕИ: 671, UN CEFACT: SET)
        'name' => ['ru' => 'Комплект', 'en' => 'Set'],
        'symbol' => ['ru' => 'компл.', 'en' => 'set'],
      ],
    ];

    foreach ($units as $unit) {
      Unit::updateOrCreate(
        ['slug' => $unit['slug']],
        [
          'code' => $unit['code'],
          'name' => $unit['name'],
          'symbol' => $unit['symbol'],
        ]
      );
    }

    $this->command->info(
      'Core: Standard Units (including m2, m, set with OKEI/UN codes) seeded successfully.'
    );
  }
}
