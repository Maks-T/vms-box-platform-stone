<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Filament\Pages\StoneSettings\Tabs;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * Вкладка настройки правил тарификации монтажа и логистических услуг.
 *
 * @since 2026-10-08
 */
class ServicesTab
{
    public static function make(): Tab
    {
        return Tab::make(__('Services and Montage'))
            ->icon('heroicon-o-wrench-screwdriver')
            ->schema([
                Section::make(__('Stone Montage Pricing'))
                    ->schema([
                        Radio::make('montage_rate_type')
                            ->label(__('Montage Rate Type'))
                            ->options([
                                'per_linear_meter' => __('Per linear meter of perimeter'),
                                'per_sq_meter' => __('Per square meter of area'),
                                'fixed' => __('Fixed rate per set'),
                            ])
                            ->required(),
                    ]),
            ]);
    }
}