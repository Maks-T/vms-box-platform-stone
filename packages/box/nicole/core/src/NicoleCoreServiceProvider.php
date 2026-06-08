<?php

declare(strict_types=1);

namespace Nicole\Box\Core;

use App\Models\User;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Nicole\Box\Core\Models\Media;
use Nicole\Box\Core\Services\PricingManager;
use Nicole\Box\Core\Support\Media\NicolePathGenerator;
use Illuminate\Support\Facades\Gate;
use Dedoc\Scramble\Scramble;
use Nicole\Box\Core\Support\Scramble\Extensions\GlobalApiExtension;

class NicoleCoreServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    $this->loadJsonTranslationsFrom(realpath(__DIR__ . '/../lang'));

    $this->mergeConfigFrom(__DIR__ . '/../config/nicole.php', 'nicole');
    $this->mergeConfigFrom(__DIR__ . '/../config/media-library.php', 'media-library');

    $this->app->singleton(PricingManager::class, fn() => new PricingManager);
    $this->app->singleton(CoreConfig::class, fn() => new CoreConfig());

    if (class_exists(Scramble::class)) {
      Scramble::registerExtension(GlobalApiExtension::class);
    }
  }

  public function boot(GateContract $gate): void
  {
    $gate->define('viewApiDocs', function (?User $user = null) {
      return true;
    });

    if (class_exists(Scramble::class)) {
      Scramble::registerExtension(GlobalApiExtension::class);
    }

    /**
     * Макрос для миграций: добавляет стандартную JSONB колонку настроек.
     * Использование в миграции: $table->settings();
     */
    Blueprint::macro('settings', function () {
      /** @var Blueprint $this */
      return $this->jsonb('settings')->nullable();
    });

    Relation::morphMap([
      'product' => \Nicole\Box\Core\Models\Product::class,
      'product_variant' => \Nicole\Box\Core\Models\ProductVariant::class,
      'category' => \Nicole\Box\Core\Models\Category::class,
      'warehouse' => \Nicole\Box\Core\Models\Warehouse::class,
      'attribute' => \Nicole\Box\Core\Models\Attribute::class,
      'media' => \Nicole\Box\Core\Models\Media::class,
      'product_type' => \Nicole\Box\Core\Models\ProductType::class,
      'pipeline' => \Nicole\Box\Core\Models\Pipeline::class,
      'family' => \Nicole\Box\Core\Models\ProductFamily::class,
      'complex_dictionary' => \Nicole\Box\Core\Models\ComplexDictionary::class,
      'complex_dictionary_record' => \Nicole\Box\Core\Models\ComplexDictionaryRecord::class,
      'price_type' => \Nicole\Box\Core\Models\PriceType::class,
      'currency' => \Nicole\Box\Core\Models\Currency::class,
      'unit' => \Nicole\Box\Core\Models\Unit::class,
      'attribute_option' => \Nicole\Box\Core\Models\AttributeOption::class,
      'stock' => \Nicole\Box\Core\Models\Stock::class,
    ]);

    if ($this->app->runningInConsole()) {
      $this->commands([
        \Nicole\Box\Core\Console\Commands\ImportCatalogCommand::class,
      ]);

      $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

      $this->publishes([__DIR__ . '/../config/nicole.php' => config_path('nicole.php')], 'nicole-config');
      $this->publishes([__DIR__ . '/../config/media-library.php' => config_path('media-library.php')], 'nicole-media-config');

      config([
        'media-library.media_model' => Media::class,
        'media-library.path_generator' => NicolePathGenerator::class,
      ]);
    }

    LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
      $switch->locales(['en', 'ru'])->visible(outsidePanels: true);
    });

    SpatieMediaLibraryFileUpload::configureUsing(function (SpatieMediaLibraryFileUpload $component) {
      $component->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']);
    });

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

