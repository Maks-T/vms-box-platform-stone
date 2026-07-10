<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    //
  }

  public function boot(): void
  {
    // Регистрируем глобальный перехват прав для роли admin (Super Admin)
    Gate::before(function ($user, $ability) {
      // Гвардо-независимая проверка: ищет роль 'admin' напрямую в коллекции связей,
      // а также использует резервную проверку по email администратора.
      if ($user->email === 'admin@vms.local' || $user->roles->contains('name', 'admin')) {
        return true;
      }
      return null;
    });

    // Настраиваем автоопределение политик для моделей из пакетов (Nicole\Box\Core\Models)
    Gate::guessPolicyNamesUsing(function (string $modelClass) {
      return 'App\\Policies\\' . class_basename($modelClass) . 'Policy';
    });
  }

}
