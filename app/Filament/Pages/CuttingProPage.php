<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

class CuttingProPage extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scissors';

    protected static ?string $slug = 'cutting-pro';

    protected static ?int $navigationSort = 15;

    protected string $view = 'filament.pages.cutting-pro';

    public static function getNavigationGroup(): ?string
    {
        return 'Каталог';
    }

    public static function getNavigationLabel(): string
    {
        return 'Раскрой';
    }

    public function getTitle(): string
    {
        return 'Профессиональный раскрой';
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