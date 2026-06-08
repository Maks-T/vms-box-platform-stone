<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Database\Seeders;

use Illuminate\Database\Seeder;
use Nicole\Box\Core\Models\ComplexDictionary;
use Nicole\Box\Core\Models\Unit;

class StoneIndustryDictionariesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info(
            'Industry Stone: Initializing specific schemas and units...',
        );

        $this->seedIndustryUnits();
        $this->seedComplexSchemas();

        $this->command->info(
            'Industry Stone: Schemas and Units seeded successfully.',
        );
    }

    private function seedIndustryUnits(): void
    {
        Unit::updateOrCreate(
            ['slug' => 'm'],
            [
                'code' => '006',
                'name' => ['ru' => 'Метр погонный', 'en' => 'Linear meter'],
                'symbol' => ['ru' => 'м.п.', 'en' => 'lm'],
            ],
        );

        Unit::updateOrCreate(
            ['slug' => 'm2'],
            [
                'code' => '055',
                'name' => ['ru' => 'Метр квадратный', 'en' => 'Square meter'],
                'symbol' => ['ru' => 'м2', 'en' => 'sqm'],
            ],
        );

        Unit::updateOrCreate(
            ['slug' => 'set'],
            [
                'code' => '839',
                'name' => ['ru' => 'Комплект', 'en' => 'Set'],
                'symbol' => ['ru' => 'компл.', 'en' => 'set'],
            ],
        );
    }

    private function seedComplexSchemas(): void
    {
        // Схема для Ценовых групп (Матрица цен)
        ComplexDictionary::updateOrCreate(
            ['code' => 'price_group'],
            [
                'name' => [
                    'ru' => 'Ценовые группы (Матрица)',
                    'en' => 'Price Categories',
                ],
                'schema' => [
                    [
                        'key' => 'material_cost',
                        'type' => 'price',
                        'label' => [
                            'ru' => 'Закупка (Материал) $',
                            'en' => 'Cost (Material) $',
                        ],
                    ],
                ],
                
                'settings' => [
                    'channels' => [
                        'widget' => ['is_enabled' => false], 
                        'catalog' => ['is_enabled' => false],
                    ],
                ],
            ],
        );

        
        ComplexDictionary::updateOrCreate(
            ['code' => 'cutting_groups'],
            [
                'name' => ['ru' => 'Группы раскроя', 'en' => 'Cutting Groups'],
                'schema' => [
                    [
                        'key' => 'rotate',
                        'type' => 'boolean',
                        'label' => ['ru' => 'Повтор рисунка', 'en' => 'Pattern Repeat'],
                    ],
                    [
                        'key' => 'cut',
                        'type' => 'boolean',
                        'label' => [
                            'ru' => 'Раздельный раскрой',
                            'en' => 'Separate Cutting',
                        ],
                    ],
                ],
                'settings' => [
                    'channels' => [
                        'widget' => ['is_enabled' => true],
                        'catalog' => ['is_enabled' => true],
                    ],
                ],
            ],
        );
    }
}
