<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ValerieStoneServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
  }

  public function boot(): void
  {
    $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    $this->loadViewsFrom(__DIR__ . '/../resources/views', 'valerie-stone');

    $this->registerApiRoutes();
    $this->registerWebRoutes();
  }

  protected function registerApiRoutes(): void
  {
    if (!$this->app->routesAreCached()) {
      Route::prefix('api/v1')
        ->middleware(['api', \Nicole\Box\Core\Http\Middleware\EnforceChannelContext::class])
        ->group(__DIR__ . '/../routes/api.php');
    }
  }

  protected function registerWebRoutes(): void
  {
    if (!$this->app->routesAreCached() && file_exists(__DIR__ . '/../routes/web.php')) {
      Route::middleware(['web'])
        ->group(__DIR__ . '/../routes/web.php');
    }
  }
}
