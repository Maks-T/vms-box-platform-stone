<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ValerieStoneServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    // Регистрация специфичных сервисов индустрии камня
  }

  public function boot(): void
  {
    $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
    $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    $this->loadViewsFrom(__DIR__ . '/../resources/views', 'valerie-stone');

    $this->registerApiRoutes();
  }

  protected function registerApiRoutes(): void
  {
    if (!$this->app->routesAreCached()) {
      Route::prefix('api/v1')
        ->middleware(['api', \Nicole\Box\Core\Http\Middleware\EnforceChannelContext::class])
        ->group(__DIR__ . '/../routes/api.php');
    }
  }
}
