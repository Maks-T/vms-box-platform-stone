<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Filament\Pages\StoneSettings\Tabs;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\HtmlString;

/**
 * Вкладка настройки технологических профилей припусков (кромка и подгиб).
 *
 * @since 2026-10-08
 */
class AllowanceProfilesTab
{
    /**
     * Реестр технологических профилей припусков.
     *
     * @return array<string, array{name: string, desc: string, default_edge: int, default_hem: int}>
     */
    public static function getRegistry(): array
    {
        return [
            'acrylic_standard' => [
                'name' => __('Acrylic Stone Profile'),
                'desc' => __('Standard allowances for acrylic solid surface with glued hem (+40mm)'),
                'default_edge' => 40,
                'default_hem' => 40,
            ],
            'quartz_standard' => [
                'name' => __('Quartz Composite Profile'),
                'desc' => __('Standard allowances for engineered quartz (no hem, 40mm miter/edge)'),
                'default_edge' => 40,
                'default_hem' => 0,
            ],
            'natural_stone' => [
                'name' => __('Natural Stone Profile'),
                'desc' => __('Allowances for marble and granite slabs with polished edge'),
                'default_edge' => 30,
                'default_hem' => 0,
            ],
        ];
    }

    /**
     * Реестр стандартных сторон изделия для обработки.
     *
     * @return array<string, string>
     */
    public static function getStandardSides(): array
    {
        return [
            'front' => __('Front (Visible edge)'),
            'left' => __('Left End'),
            'right' => __('Right End'),
            'back' => __('Back (Wall side)'),
            'ears' => __('Ears / Side reveals'),
        ];
    }

    public static function make(?array $profilesRegistry = null, ?array $standardSides = null): Tab
    {
        $profilesRegistry ??= static::getRegistry();
        $standardSides ??= static::getStandardSides();
        $profilesTabs = [];

        foreach ($profilesRegistry as $profileSlug => $profileDef) {
            $sideInputs = [];
            foreach ($standardSides as $sideKey => $sideTitle) {
                $sideInputs[] = Grid::make(12)->schema([
                    TextEntry::make('profiles.' . $profileSlug . '.sides.' . $sideKey . '.title')
                        ->hiddenLabel()
                        ->columnSpan(4)
                        ->state(fn () => new HtmlString("<div class='pt-2 text-xs font-semibold text-gray-800 dark:text-gray-200'>{$sideTitle}</div>")),

                    Checkbox::make('profiles.' . $profileSlug . '.sides.' . $sideKey . '.has_edge')
                        ->label(__('Edge'))
                        ->columnSpan(2),

                    TextInput::make('profiles.' . $profileSlug . '.sides.' . $sideKey . '.edge_width')
                        ->label(__('Edge, mm'))
                        ->numeric()
                        ->default($profileDef['default_edge'])
                        ->columnSpan(2),

                    Checkbox::make('profiles.' . $profileSlug . '.sides.' . $sideKey . '.has_hem')
                        ->label(__('Hem'))
                        ->columnSpan(2),

                    TextInput::make('profiles.' . $profileSlug . '.sides.' . $sideKey . '.hem_width')
                        ->label(__('Hem, mm'))
                        ->numeric()
                        ->default($profileDef['default_hem'])
                        ->columnSpan(2),
                ]);
            }

            $profilesTabs[] = Tab::make($profileSlug)
                ->label($profileDef['name'])
                ->schema([
                    Section::make($profileDef['name'])
                        ->description($profileDef['desc'])
                        ->schema($sideInputs),
                ]);
        }

        return Tab::make(__('Allowance Profiles'))
            ->icon('heroicon-o-swatch')
            ->schema([
                Tabs::make('ProfilesInnerTabs')->tabs($profilesTabs),
            ]);
    }
}