<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Tests\Traits;

use App\Models\User;
use Nicole\Box\Core\Models\Role;

trait InteractsWithFilamentAdmin
{
  protected User $adminUser;

  /**
   * Инициализирует администратора панели и сбрасывает кэш разрешений.
   */
  protected function setUpFilamentAdmin(): void
  {
    // Сбрасываем кэш ролей Spatie
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    // Создаем администратора панели
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    /** @var User $user */
    $user = User::factory()->create();

    $this->adminUser = $user;
    $this->adminUser->assignRole($adminRole);
  }

}