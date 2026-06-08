<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Nicole\Box\Core\Models\Warehouse;
use Valerie\Box\IndustryStone\Database\Seeders\StoneIndustryServicesSeeder;

class DatabaseSeeder extends Seeder
{
  public function run(): void
  {
    // 1. Уровень Ядра
    $this->call([
      \Nicole\Box\Core\Database\Seeders\NicoleCoreSeeder::class,
      \Nicole\Box\Core\Database\Seeders\CommerceInfrastructureSeeder::class,
    ]);

    // 2. Уровень Индустрии
    $this->call([
      /*\Valerie\Box\IndustryStone\Database\Seeders\StoneIndustryDictionariesSeeder::class,
      \Valerie\Box\IndustryStone\Database\Seeders\StoneIndustryDataImportSeeder::class,
      \Valerie\Box\IndustryStone\Database\Seeders\StoneIndustryServicesSeeder::class,
      \Valerie\Box\IndustryStone\Database\Seeders\StoneIndustryImagesSeeder::class,*/
    ]);

    $this->command->info('Database seeding completed successfully!');
  }
}
