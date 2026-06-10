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
      return $user->hasRole('admin') ? true : null;
    });
  }

}
