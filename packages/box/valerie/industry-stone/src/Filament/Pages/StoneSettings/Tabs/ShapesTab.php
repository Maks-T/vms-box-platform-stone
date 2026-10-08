<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Filament\Pages\StoneSettings\Tabs;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\HtmlString;

/**
 * Вкладка настройки геометрических форм изделий и размерных лимитов.
 *
 * @since 2026-10-08
 */
class ShapesTab
{
    /**
     * Реестр геометрических форм изделий из камня.
     *
     * @return array<string, array{name: string, desc: string, blueprint: string}>
     */
    public static function getRegistry(): array
    {
        return [
            'worktop_line' => [
                'name' => __('Straight Worktop'),
                'desc' => __('Straight worktop for kitchen or bathroom along a single wall'),
                'blueprint' => asset('pdf/layouts/worktop/line.png'),
            ],
            'worktop_l_shaped' => [
                'name' => __('L-Shaped Worktop'),
                'desc' => __('Corner construction of two joined wings with a seam'),
                'blueprint' => asset('pdf/layouts/worktop/l_shaped.png'),
            ],
            'worktop_u_shaped' => [
                'name' => __('U-Shaped Worktop'),
                'desc' => __('Three-section construction with two corner joints'),
                'blueprint' => asset('pdf/layouts/worktop/u_shaped.png'),
            ],
            'island' => [
                'name' => __('Kitchen Island'),
                'desc' => __('Freestanding island module with perimeter processing'),
                'blueprint' => asset('pdf/layouts/worktop/line.png'),
            ],
            'bar_counter' => [
                'name' => __('Bar Counter'),
                'desc' => __('Narrow cantilever or wall panel with front edge'),
                'blueprint' => asset('pdf/layouts/worktop/line.png'),
            ],
            'windowsill_line' => [
                'name' => __('Straight Windowsill'),
                'desc' => __('Rectangular windowsill with front drip edge and ears'),
                'blueprint' => asset('pdf/layouts/windowsill/line.png'),
            ],
            'windowsill_corner' => [
                'name' => __('Corner Windowsill'),
                'desc' => __('Corner windowsill for 90 degree bay or balcony unit'),
                'blueprint' => asset('pdf/layouts/windowsill/corner.png'),
            ],
            'windowsill_bay' => [
                'name' => __('Bay Windowsill'),
                'desc' => __('Multi-section bay windowsill with obtuse corner joints'),
                'blueprint' => asset('pdf/layouts/windowsill/bay.png'),
            ],
            'wall_panel' => [
                'name' => __('Wall Panel (Backsplash)'),
                'desc' => __('Vertical wall panel between worktop and upper cabinets'),
                'blueprint' => asset('pdf/layouts/worktop/line.png'),
            ],
        ];
    }

    public static function make(?array $shapesRegistry = null): Tab
    {
        $shapesRegistry ??= static::getRegistry();
        $shapesTabs = [];

        foreach ($shapesRegistry as $shapeSlug => $shapeDef) {
            $upperCode = strtoupper($shapeSlug);
            $visualHtml = '
                <div style="display:flex;align-items:center;gap:16px;padding:12px 16px;border-radius:12px;border:1px solid rgba(156,163,175,0.25);background:rgba(243,244,246,0.6);width:100%;box-sizing:border-box;">
                    <div style="width:180px;height:120px;min-width:180px;padding:8px;border-radius:10px;border:1px solid rgba(156,163,175,0.2);background:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
                        <img src="' . $shapeDef['blueprint'] . '" style="max-width:100%;max-height:100%;object-fit:contain;" alt="' . $shapeDef['name'] . '" />
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;min-width:0;flex:1;">
                        <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#0284c7;font-family:monospace;">' . __('Code') . ': ' . $upperCode . '</span>
                        <h4 style="margin:0;font-size:16px;font-weight:700;line-height:1.3;">' . $shapeDef['name'] . '</h4>
                        <p style="margin:0;font-size:13px;line-height:1.4;color:#6b7280;">' . $shapeDef['desc'] . '</p>
                    </div>
                </div>';

            $shapesTabs[] = Tab::make($shapeSlug)
                ->label($shapeDef['name'])
                ->schema([
                    Section::make()->schema([
                        Grid::make(12)->schema([
                            TextEntry::make('shapes.' . $shapeSlug . '.visual_card')
                                ->hiddenLabel()
                                ->columnSpan(['default' => 12, 'lg' => 7])
                                ->state(fn () => new HtmlString($visualHtml)),

                            Grid::make(1)
                                ->columnSpan(['default' => 12, 'lg' => 5])
                                ->schema([
                                    Toggle::make('shapes.' . $shapeSlug . '.is_active')
                                        ->label(__('Active in calculator'))
                                        ->helperText(__('If disabled, the shape will be completely hidden from the calculator wizard'))
                                        ->default(true),
                                ]),
                        ]),
                    ]),

                    Section::make(__('Dimensional Limits'))
                        ->description(__('Limits the min and max dimensions in the configurator wizard'))
                        ->schema([
                            Grid::make(4)->schema([
                                TextInput::make('shapes.' . $shapeSlug . '.length_min')->label(__('Min Length (mm)'))->numeric()->required(),
                                TextInput::make('shapes.' . $shapeSlug . '.length_max')->label(__('Max Length (mm)'))->numeric()->required(),
                                TextInput::make('shapes.' . $shapeSlug . '.width_min')->label(__('Min Width (mm)'))->numeric()->required(),
                                TextInput::make('shapes.' . $shapeSlug . '.width_max')->label(__('Max Width (mm)'))->numeric()->required(),
                            ]),
                        ]),

                    Section::make(__('Allowed Services & Geometry Engine'))
                        ->description(__('Defines the math decomposition type and services permitted for this shape'))
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('shapes.' . $shapeSlug . '.geometry_type')->label(__('Geometry Type (line, l_shaped...)'))->default('line')->required(),
                                TextInput::make('shapes.' . $shapeSlug . '.category_scope')->label(__('Category Scope (kitchen, windowsill...)'))->default('kitchen')->required(),
                                TextInput::make('shapes.' . $shapeSlug . '.allowed_services')->label(__('Allowed Service Codes (comma separated)'))->placeholder('cutout_price,cutout_hob_price...')->columnSpan(1),
                            ]),
                        ]),
                ]);
        }

        return Tab::make(__('Shapes and Limits'))
            ->icon('heroicon-o-cube-transparent')
            ->schema([
                Tabs::make('ShapesInnerTabs')->tabs($shapesTabs),
            ]);
    }
}