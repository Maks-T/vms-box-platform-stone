<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Nicole\Box\Core\Models\Channel;
use Nicole\Box\Core\Models\Role;
use Nicole\Box\Core\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class NicoleCoreSeeder extends Seeder
{
  public function run(): void
  {

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    // 1. Убеждаемся в наличии роли admin (Super Admin)
    $adminRole = Role::firstOrCreate(
      ['name' => 'admin'],
      ['guard_name' => 'web']
    );

    // 2. Создаем Super Admin пользователя
    $adminUser = User::firstOrCreate(
      ['email' => 'admin@vms.local'],
      [
        'name' => 'System Administrator',
        'password' => Hash::make('password'),
      ]
    );
    $adminUser->assignRole($adminRole);

    // 3. Настройка роли Контент-менеджера (Управление каталогом и его настройками)
    $contentManagerRole = Role::firstOrCreate(
      ['name' => 'content_manager'],
      ['guard_name' => 'web']
    );

    // Список сущностей, к которым контент-менеджеру нужен полный доступ
    $catalogModels = [
      'Product',
      'ProductVariant',
      'Category',
      'ProductFamily',
      'ProductType',
      'Attribute',
      'ComplexDictionary',
      'PriceGroup',
      'Unit',
      'Currency',
      'Warehouse',
      'PriceType',
      'SettingSchema'
    ];

    // Динамически выбираем из базы данных права, сгенерированные Shield для этих моделей
    $contentPermissions = Permission::where(function ($query) use ($catalogModels) {
      foreach ($catalogModels as $model) {
        $query->orWhere('name', 'like', "%:{$model}");
      }
    })->pluck('name')->toArray();

    $contentManagerRole->syncPermissions($contentPermissions);

    // Создаем дефолтного пользователя для контент-менеджера
    $contentUser = User::firstOrCreate(
      ['email' => 'content@vms.local'],
      [
        'name' => 'Контент-менеджер',
        'password' => Hash::make('password'),
      ]
    );
    $contentUser->assignRole($contentManagerRole);


    // 4. Настройка роли Дилера (Менеджера) (Только виджет, заказы и клиенты)
    $dealerRole = Role::firstOrCreate(
      ['name' => 'dealer'],
      ['guard_name' => 'web']
    );

    // Находим права для Заказов и Клиентов
    $dealerModels = ['Order', 'Customer'];
    $dealerPermissions = Permission::where(function ($query) use ($dealerModels) {
      foreach ($dealerModels as $model) {
        $query->orWhere('name', 'like', "%:{$model}");
      }
    })->pluck('name')->toArray();

    // Добавляем только права на просмотр статусов заказов (без возможности их изменения)
    $statusReadPermissions = Permission::where(function ($query) {
      $query->where('name', 'View:OrderStatus')
        ->orWhere('name', 'ViewAny:OrderStatus');
    })->pluck('name')->toArray();

    // Добавляем права на просмотр Dashboard/виджетов, если они сгенерированы Shield
    $dashboardPermissions = Permission::where('name', 'like', '%Dashboard%')
      ->orWhere('name', 'like', '%Widget%')
      ->pluck('name')
      ->toArray();

    $finalDealerPermissions = array_merge($dealerPermissions, $statusReadPermissions, $dashboardPermissions);
    $dealerRole->syncPermissions($finalDealerPermissions);

    // Создаем дефолтного пользователя Дилера (Менеджера)
    $dealerUser = User::firstOrCreate(
      ['email' => 'dealer@vms.local'],
      [
        'name' => 'Дилер (Менеджер)',
        'password' => Hash::make('password'),
      ]
    );
    $dealerUser->assignRole($dealerRole);


    // 5. Инициализируем каналы продаж
    Channel::updateOrCreate(
      ['code' => 'widget'],
      [
        'name' => ['ru' => 'Виджет калькулятора', 'en' => 'Calculator Widget'],
        'is_active' => true,
      ]
    );

    Channel::updateOrCreate(
      ['code' => 'catalog'],
      [
        'name' => ['ru' => 'Основной сайт', 'en' => 'Main Web Catalog'],
        'is_active' => true,
      ]
    );

    $this->command->info(
      'Nicole Core: Roles and default users synced successfully with Shield permissions:' . PHP_EOL .
      '  - admin@vms.local / password (Super Admin)' . PHP_EOL .
      '  - content@vms.local / password (Content Manager)' . PHP_EOL .
      '  - dealer@vms.local / password (Dealer / Manager)'
    );
  }

}
