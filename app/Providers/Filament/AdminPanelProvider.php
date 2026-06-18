<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Nicole\Box\Core\NicoleCorePlugin;
use Valerie\Box\IndustryStone\ValerieStonePlugin;
use Outerweb\FilamentTranslatableFields\TranslatableFieldsPlugin;

class AdminPanelProvider extends PanelProvider
{
  public function panel(Panel $panel): Panel
  {
    return $panel
      ->default()
      ->id('admin')
      ->path('admin')
      ->login()
      ->colors([
        'primary' => Color::Blue,
      ])
      ->maxContentWidth(Width::Full)
      ->brandLogo(asset('images/logo.svg'))
      ->brandLogoHeight('3rem')
      ->homeUrl('/')
      ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
      ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
      ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
      ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
      ->widgets([
        Widgets\AccountWidget::class,
        Widgets\FilamentInfoWidget::class,
      ])
      ->middleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        AuthenticateSession::class,
        ShareErrorsFromSession::class,
        PreventRequestForgery::class,
        SubstituteBindings::class,
        DisableBladeIconComponents::class,
        DispatchServingFilamentEvent::class,
      ])
      ->authMiddleware([
        Authenticate::class,
      ])
      ->pages([
        Dashboard::class,
      ])
      ->navigationGroups([
        NavigationGroup::make()
          ->label(fn (): string => __('Catalog')),


        NavigationGroup::make()
          ->label(fn (): string => __('Catalog Settings'))
          ->collapsed(),

        NavigationGroup::make()
          ->label(fn (): string => __('Inventory')),


        NavigationGroup::make()
          ->label(fn (): string => __('Access Control'))
          ->collapsed(),
      ])
      ->plugins([
        NicoleCorePlugin::make(),
        ValerieStonePlugin::make(),

        SpatieTranslatablePlugin::make()
          ->defaultLocales(['ru', 'en']),

        TranslatableFieldsPlugin::make()
          ->supportedLocales(['ru', 'en']),

        FilamentShieldPlugin::make()->navigationGroup('Контроль доступа'),
      ]);
  }
}
