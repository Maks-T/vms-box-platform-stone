<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Filament\Pages\StoneSettings\Tabs;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Nicole\Box\Core\Models\ProductType;

/**
 * Вкладка настройки физики слэбов типов камня с прямой записью в ProductType.meta.
 *
 * @since 2026-10-08
 */
class MaterialsTab
{
    /**
     * Генерация суб-табов по типам камня, привязанным к семейству камней.
     *
     * @param iterable<\Nicole\Box\Core\Models\ProductType>|null $stoneTypes Предзагруженная коллекция типов камня
     * @param array<string, string> $profileOptions Словарь доступных профилей [slug => label]
     * @return Tab
     */
    public static function make(?iterable $stoneTypes = null, array $profileOptions = []): Tab
    {
        $stoneTypes ??= ProductType::query()
            ->whereHas('family', fn ($q) => $q->whereIn('code', ['stone', 'natural-stone']))
            ->get();

        $materialsTabs = [];
        $locale = app()->getLocale();

        foreach ($stoneTypes as $type) {
            $typeName = $type->getTranslation('name', $locale) ?: $type->name;

            $materialsTabs[] = Tab::make($type->code)
                ->label($typeName)
                ->schema([
                    Section::make(__('Cutting Physics & Sales Step'))
                        ->schema([
                            Grid::make(12)->schema([
                                TextInput::make('materials.' . $type->code . '.step')
                                    ->label(__('Sale Step (0.25 / 0.5 / 1.0)'))
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(['default' => 12, 'md' => 4, 'xl' => 2]),

                                TextInput::make('materials.' . $type->code . '.minPart')
                                    ->label(__('Min Part Size (mm)'))
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(['default' => 12, 'md' => 4, 'xl' => 2]),

                                TextInput::make('materials.' . $type->code . '.maxStack')
                                    ->label(__('Max Stack'))
                                    ->numeric()
                                    ->default(1)
                                    ->columnSpan(['default' => 12, 'md' => 4, 'xl' => 2]),

                                Radio::make('materials.' . $type->code . '.axisX')
                                    ->label(__('Cutting Axis Direction'))
                                    ->options([
                                        1 => __('Along length (Axis X)'),
                                        0 => __('Across width (Axis Y)'),
                                    ])
                                    ->inline()
                                    ->columnSpan(['default' => 12, 'md' => 6, 'xl' => 3]),

                                Toggle::make('materials.' . $type->code . '.allow_rounding')
                                    ->label(__('Allow Corner Roundings (R1-R8)'))
                                    ->inline(false)
                                    ->columnSpan(['default' => 6, 'md' => 3, 'xl' => 1]),

                                Toggle::make('materials.' . $type->code . '.is_separate')
                                    ->label(__('Cut Separately from Wall Panel'))
                                    ->inline(false)
                                    ->columnSpan(['default' => 6, 'md' => 3, 'xl' => 2]),
                            ]),
                        ]),

                    Section::make(__('Technological Allowances & Transport'))
                        ->schema([
                            Grid::make(12)->schema([
                                TextInput::make('materials.' . $type->code . '.trim_offset')
                                    ->label(__('Trim Offset (mm)'))
                                    ->helperText(__('Perimeter margin cut from raw factory slab'))
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(['default' => 12, 'md' => 6, 'xl' => 3]),

                                TextInput::make('materials.' . $type->code . '.max_transport_size')
                                    ->label(__('Max Transport Size (mm)'))
                                    ->helperText(__('Indivisible part length limit. Exceeding parts will be cut on site.'))
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(['default' => 12, 'md' => 6, 'xl' => 3]),

                                TextInput::make('materials.' . $type->code . '.corner_add_length')
                                    ->label(__('Inner Corner Add Length (mm)'))
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(['default' => 12, 'md' => 6, 'xl' => 3]),

                                TextInput::make('materials.' . $type->code . '.corner_add_width')
                                    ->label(__('Inner Corner Add Width (mm)'))
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(['default' => 12, 'md' => 6, 'xl' => 3]),

                                Select::make('materials.' . $type->code . '.allowance_profile')
                                    ->label(__('Assigned Allowance Profile'))
                                    ->options($profileOptions)
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(['default' => 12, 'md' => 8, 'xl' => 6]),
                            ]),
                        ]),
                ]);
        }

        return Tab::make(__('Materials & Slabs'))
            ->icon('heroicon-o-scissors')
            ->schema([
                Tabs::make('MaterialsInnerTabs')->tabs($materialsTabs),
            ]);
    }
}