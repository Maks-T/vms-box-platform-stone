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
    // Глобальный супер-админ (доступ ко всему)
    Gate::before(function ($user, $ability) {
      return $user->hasRole('admin') ? true : null;
    });

    // Связываем модели ядра Nicole Core с политиками в App\Policies
    Gate::guessPolicyNamesUsing(function (string $modelClass) {
      $classBasename = class_basename($modelClass);
      $policyClass = "App\\Policies\\{$classBasename}Policy";

      return class_exists($policyClass) ? $policyClass : null;
    });
  }

}
