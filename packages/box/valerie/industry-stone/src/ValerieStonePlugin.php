<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone;

use Filament\Contracts\Plugin;
use Filament\Panel;

class ValerieStonePlugin implements Plugin
{
    public function getId(): string
    {
        return 'valerie-box-industry-stone';
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: __DIR__.'/Filament/Resources',
            for: 'Valerie\\Box\\IndustryStone\\Filament\\Resources',
        );

        $panel->discoverPages(
            in: __DIR__.'/Filament/Pages',
            for: 'Valerie\\Box\\IndustryStone\\Filament\\Pages',
        );
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static;
    }
}
