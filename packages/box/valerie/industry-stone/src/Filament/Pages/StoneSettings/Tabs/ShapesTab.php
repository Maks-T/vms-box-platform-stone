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
use Nicole\Box\Core\Models\Product;

/**
 * Вкладка настройки геометрических форм изделий и размерных лимитов.
 *
 * @since 2026-10-08
 */
class ShapesTab
{
    /**
     * Реестр геометрических форм изделий из камня с группировкой по категориям.
     *
     * @return array<string, array{group: string, name: string, desc: string, blueprint: string}>
     */
    public static function getRegistry(): array
    {
        return [
            'worktop_line' => [
                'group' => 'worktop',
                'name' => __('Straight Worktop'),
                'desc' => __('Straight worktop for kitchen or bathroom along a single wall'),
                'blueprint' => asset('pdf/layouts/worktop/line.png'),
            ],
            'worktop_l_shaped' => [
                'group' => 'worktop',
                'name' => __('L-Shaped Worktop'),
                'desc' => __('Corner construction of two joined wings with a seam'),
                'blueprint' => asset('pdf/layouts/worktop/l_shaped.png'),
            ],
            'worktop_u_shaped' => [
                'group' => 'worktop',
                'name' => __('U-Shaped Worktop'),
                'desc' => __('Three-section construction with two corner joints'),
                'blueprint' => asset('pdf/layouts/worktop/u_shaped.png'),
            ],
            'island' => [
                'group' => 'modules',
                'name' => __('Kitchen Island'),
                'desc' => __('Freestanding island module with perimeter processing'),
                'blueprint' => asset('pdf/layouts/island/island.webp'),
            ],
            'bar_counter' => [
                'group' => 'modules',
                'name' => __('Bar Counter'),
                'desc' => __('Narrow cantilever or wall panel with front edge'),
                'blueprint' => asset('pdf/layouts/bar/bar.webp'),
            ],
            'windowsill_line' => [
                'group' => 'windowsill',
                'name' => __('Straight Windowsill'),
                'desc' => __('Rectangular windowsill with front drip edge and ears'),
                'blueprint' => asset('pdf/layouts/windowsill/line.png'),
            ],
            'windowsill_corner' => [
                'group' => 'windowsill',
                'name' => __('Corner Windowsill'),
                'desc' => __('Corner windowsill for 90 degree bay or balcony unit'),
                'blueprint' => asset('pdf/layouts/windowsill/corner.png'),
            ],
            'windowsill_bay' => [
                'group' => 'windowsill',
                'name' => __('Bay Windowsill'),
                'desc' => __('Multi-section bay windowsill with obtuse corner joints'),
                'blueprint' => asset('pdf/layouts/windowsill/bay.png'),
            ],
            'wall_panel' => [
                'group' => 'modules',
                'name' => __('Wall Panel (Backsplash)'),
                'desc' => __('Vertical wall panel between worktop and upper cabinets'),
                'blueprint' => asset('pdf/layouts/panels/panel.webp'),
            ],
        ];
    }

    public static function make(?array $shapesRegistry = null): Tab
    {
        $shapesRegistry ??= static::getRegistry();

        $groupDefinitions = [
            'worktop' => [
                'title' => __('Worktops (Kitchen & Bath)'),
                'icon' => 'heroicon-o-rectangle-stack',
            ],
            'modules' => [
                'title' => __('Kitchen Addon Modules'),
                'icon' => 'heroicon-o-puzzle-piece',
            ],
            'windowsill' => [
                'title' => __('Windowsills'),
                'icon' => 'heroicon-o-window',
            ],
        ];

        $categoryTabs = [];
        foreach ($groupDefinitions as $groupKey => $groupInfo) {
            $groupShapes = [];
            foreach ($shapesRegistry as $shapeSlug => $shapeDef) {
                if (($shapeDef['group'] ?? 'worktop') !== $groupKey) {
                    continue;
                }
                $groupShapes[] = static::buildShapeTab($shapeSlug, $shapeDef);
            }

            $categoryTabs[] = Tab::make('group_' . $groupKey)
                ->label($groupInfo['title'])
                ->icon($groupInfo['icon'])
                ->schema([
                    Tabs::make('InnerTabs_' . $groupKey)->tabs($groupShapes),
                ]);
        }

        return Tab::make(__('Shapes and Limits'))
            ->icon('heroicon-o-cube-transparent')
            ->schema([
                Tabs::make('ShapesGroupTabs')->tabs($categoryTabs),
            ]);
    }

    protected static function buildShapeTab(string $shapeSlug, array $shapeDef): Tab
    {
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

            return Tab::make($shapeSlug)
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
                            Grid::make(12)->schema([
                                TextInput::make('shapes.' . $shapeSlug . '.length_min')
                                    ->label(__('Min Length (mm)'))
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(['default' => 6, 'md' => 3, 'xl' => 2]),

                                TextInput::make('shapes.' . $shapeSlug . '.length_max')
                                    ->label(__('Max Length (mm)'))
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(['default' => 6, 'md' => 3, 'xl' => 2]),

                                TextInput::make('shapes.' . $shapeSlug . '.width_min')
                                    ->label(__('Min Width (mm)'))
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(['default' => 6, 'md' => 3, 'xl' => 2]),

                                TextInput::make('shapes.' . $shapeSlug . '.width_max')
                                    ->label(__('Max Width (mm)'))
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(['default' => 6, 'md' => 3, 'xl' => 2]),
                            ]),
                        ]),

                    Section::make(__('Allowed Services & Geometry Engine'))
                        ->description(__('Defines the math decomposition type and services permitted for this shape'))
                        ->schema([
                            Grid::make(12)->schema([
                                Select::make('shapes.' . $shapeSlug . '.geometry_type')
                                    ->label(__('Geometry Type'))
                                    ->options([
                                        'line' => __('Straight (Line)'),
                                        'l_shaped' => __('L-Shaped (Corner seam)'),
                                        'u_shaped' => __('U-Shaped (Three sections)'),
                                        'island' => __('Island (Perimeter finish)'),
                                        'corner' => __('Corner Windowsill'),
                                        'bay' => __('Bay Windowsill'),
                                    ])
                                    ->default('line')
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(['default' => 12, 'md' => 6, 'xl' => 4]),

                                Select::make('shapes.' . $shapeSlug . '.category_scope')
                                    ->label(__('Category Scope'))
                                    ->options([
                                        'kitchen' => __('Kitchen'),
                                        'bathroom' => __('Bathroom'),
                                        'windowsill' => __('Windowsills'),
                                    ])
                                    ->default('kitchen')
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(['default' => 12, 'md' => 6, 'xl' => 4]),
                            ]),

                            Select::make('shapes.' . $shapeSlug . '.allowed_services')
                                ->label(__('Allowed Services for this Shape'))
                                ->helperText(__('Select services available for this shape in the calculator'))
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->options(function () {
                                    $locale = app()->getLocale();
                                    return Product::query()
                                        ->where('catalog_type', 'service')
                                        ->where('is_active', true)
                                        ->get()
                                        ->mapWithKeys(function ($p) use ($locale) {
                                            $name = $p->getTranslation('name', $locale) ?: $p->name;
                                            return [$p->code => "{$name} ({$p->code})"];
                                        })
                                        ->toArray();
                                })
                                ->columnSpanFull(),
                        ]),
                ]);
    }
}