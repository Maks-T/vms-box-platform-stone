<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Filament\Pages\StoneSettings\Tabs;

use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * Вкладка настройки матрицы прав видимости интерфейса калькулятора по ролям.
 *
 * @since 2026-10-08
 */
class PermissionsTab
{
    /**
     * Реестр зон интерфейса калькулятора.
     *
     * @return array<string, array<string, array{label: string, default_user: bool}>>
     */
    public static function getZones(): array
    {
        return [
            __('Pricing and Estimate') => [
                'price_materials' => ['label' => __('Display retail slab price in catalog'), 'default_user' => true],
                'price_breakdown' => ['label' => __('Cost breakdown card (Materials, Works, Montage)'), 'default_user' => false],
                'estimate_table' => ['label' => __('Detailed estimate specification table'), 'default_user' => false],
                'total_amount' => ['label' => __('Total order amount bar'), 'default_user' => true],
            ],
            __('Additional Manufacturing Options') => [
                'show_sinks_step' => ['label' => __('Sink and faucet selection step'), 'default_user' => true],
                'show_wall_panel_step' => ['label' => __('Wall panel (backsplash) selection step'), 'default_user' => true],
                'show_bar_counter_step' => ['label' => __('Bar counter / island selection step'), 'default_user' => true],
                'show_edge_step' => ['label' => __('Decorative edge profile selection step'), 'default_user' => true],
            ],
            __('Export and Action Buttons') => [
                'btn_download_pdf' => ['label' => __('Download PDF quote button'), 'default_user' => false],
                'btn_print_html' => ['label' => __('Print quote HTML button'), 'default_user' => false],
                'btn_share_link' => ['label' => __('Share calculation link button'), 'default_user' => true],
            ],
        ];
    }

    public static function make(?array $interfaceZones = null): Tab
    {
        $interfaceZones ??= static::getZones();

        return Tab::make(__('Interface Visibility (Permissions)'))
            ->icon('heroicon-o-eye')
            ->schema(
                collect($interfaceZones)->map(function (array $zones, string $groupName) {
                    return Section::make($groupName)
                        ->compact()
                        ->schema(
                            collect($zones)->map(function (array $info, string $key) {
                                return Grid::make(4)->schema([
                                    Section::make($info['label'])->columnSpan(1)->compact(),
                                    Checkbox::make('ui_matrix.' . $key . '.userShow')->label(__('User (Website)'))->columnSpan(1),
                                    Checkbox::make('ui_matrix.' . $key . '.managerShow')->label(__('Manager (CRM)'))->columnSpan(1),
                                    Checkbox::make('ui_matrix.' . $key . '.adminShow')->label(__('Administrator'))->columnSpan(1),
                                ]);
                            })->values()->toArray()
                        );
                })->values()->toArray()
            );
    }
}