<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Filament\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

/**
 * Страница профессионального раскроя слэбов камня в панели управления.
 *
 * @since 2026-10-12
 */
class CuttingProPage extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scissors';

    protected static ?string $slug = 'cutting-pro';

    protected static ?int $navigationSort = 15;

    protected string $view = 'valerie-stone::filament.pages.cutting-pro';

    public static function getNavigationGroup(): ?string
    {
        return __('Catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('Раскрой');
    }

    public function getTitle(): string
    {
        return __('Профессиональный раскрой');
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getHeading(): string
    {
        return '';
    }
}