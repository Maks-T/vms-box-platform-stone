<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Nicole\Box\Core\Models\ComplexDictionary;
use Nicole\Box\Core\Models\ComplexDictionaryRecord;
use Nicole\Box\Core\Support\CatalogCache;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

/**
 * Единый оркестратор фабрики камнеобработки: формы, припуски сторон, лимиты слэбов и права.
 *
 * @property Schema $form
 */
class ManageStoneCalculatorSettings extends Page implements HasForms
{
  use InteractsWithForms;
  use HasPageShield;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

  protected static ?string $slug = 'stone/calculator-settings';

  protected static ?int $navigationSort = 10;

  protected string $view = 'valerie-stone::filament.pages.manage-stone-settings';

  public static function getNavigationGroup(): ?string
  {
    return __('Configurations');
  }

  public static function getNavigationLabel(): string
  {
    return __('Stone Calculator Settings');
  }

  public ?array $data = [];

  public function mount(): void
  {
    $this->form->fill($this->loadCurrentSettings());
  }

  /**
   * Реестр поддерживаемых форм каменных изделий.
   *
   * @return array<string, array{name: string, desc: string, icon: string, blueprint: ?string, sides: array<string, string>}>
   */
  protected function getStoneShapesRegistry(): array
  {
    return [
      'worktop_line' => [
        'name' => __('Straight Worktop'),
        'desc' => __('Straight worktop for kitchen or bathroom along a single wall'),
        'image' => asset('pdf/layouts/worktop/line.png'),
        'sides' => [
          'front' => __('Front (Visible edge)'),
          'left' => __('Left End'),
          'right' => __('Right End'),
          'back' => __('Back (Wall skirting)'),
        ],
      ],
      'worktop_l_shaped' => [
        'name' => __('L-Shaped Worktop'),
        'desc' => __('Corner construction of two joined wings with a seam'),
        'image' => asset('pdf/layouts/worktop/l_shaped.png'),
        'sides' => [
          'front' => __('Front visible edges'),
          'left' => __('Left outer end'),
          'right' => __('Right outer end'),
          'back' => __('Wall sides (skirtings)'),
          'inner_corner' => __('Inner corner joint'),
        ],
      ],
      'worktop_u_shaped' => [
        'name' => __('U-Shaped Worktop'),
        'desc' => __('Three-section construction with two corner joints'),
        'image' => asset('pdf/layouts/worktop/u_shaped.png'),
        'sides' => [
          'front' => __('Front visible edges'),
          'left' => __('Left open end'),
          'right' => __('Right open end'),
          'back' => __('Wall edges'),
        ],
      ],
      'island' => [
        'name' => __('Kitchen Island'),
        'desc' => __('Freestanding island module with perimeter processing'),
        'image' => asset('pdf/layouts/worktop/line.png'),
        'sides' => [
          'front' => __('Side A (Front)'),
          'back' => __('Side B (Back)'),
          'left' => __('Left End'),
          'right' => __('Right End'),
        ],
      ],
      'bar_counter' => [
        'name' => __('Bar Counter'),
        'desc' => __('Narrow cantilever or wall panel with front edge'),
        'image' => asset('pdf/layouts/worktop/line.png'),
        'sides' => [
          'front' => __('Front overhang'),
          'back' => __('Back overhang'),
          'left' => __('Side overhang'),
          'right' => __('Side overhang'),
        ],
      ],
      'windowsill_line' => [
        'name' => __('Straight Windowsill'),
        'desc' => __('Rectangular windowsill with front drip edge and ears'),
        'image' => asset('pdf/layouts/windowsill/line.png'),
        'sides' => [
          'front' => __('Front edge (Capinos)'),
          'ears' => __('Side ears (Notches)'),
          'left' => __('Left End'),
          'right' => __('Right End'),
          'back' => __('Sub-frame back side'),
        ],
      ],
      'windowsill_corner' => [
        'name' => __('Corner Windowsill'),
        'desc' => __('Corner windowsill for 90 degree bay or balcony unit'),
        'image' => asset('pdf/layouts/windowsill/corner.png'),
        'sides' => [
          'front' => __('Front visible edge'),
          'ears' => __('Side notches (ears)'),
          'back' => __('Window frame joint'),
        ],
      ],
      'windowsill_bay' => [
        'name' => __('Bay Windowsill'),
        'desc' => __('Multi-section bay windowsill with obtuse corner joints'),
        'image' => asset('pdf/layouts/windowsill/bay.png'),
        'sides' => [
          'front' => __('Front perimeter'),
          'ears' => __('Side reveals overhang'),
          'back' => __('Glazing unit joint'),
        ],
      ],
      'wall_panel' => [
        'name' => __('Wall Panel (Backsplash)'),
        'desc' => __('Vertical wall panel between worktop and upper cabinets'),
        'image' => asset('pdf/layouts/worktop/line.png'),
        'sides' => [
          'top' => __('Top visible end'),
          'bottom' => __('Bottom worktop joint'),
          'left' => __('Left End'),
          'right' => __('Right End'),
        ],
      ],
    ];
  }

  protected function loadCurrentSettings(): array
  {
    /** @var ComplexDictionary|null $shapesDict */
    $shapesDict = ComplexDictionary::query()
      ->where('code', 'stone_shapes_config')
      ->with('records')
      ->first();
    $shapeRecords = $shapesDict?->records?->keyBy('slug') ?? collect();

    /** @var ComplexDictionary|null $interfaceDict */
    $interfaceDict = ComplexDictionary::query()
      ->where('code', 'stone_interface_settings')
      ->with('records')
      ->first();
    $interfaceRecords = $interfaceDict?->records?->keyBy('slug') ?? collect();

    $shapesState = [];
    foreach ($this->getStoneShapesRegistry() as $shapeSlug => $shapeDef) {
      $rec = $shapeRecords->get($shapeSlug);
      $meta = $rec?->meta ?? [];

      $allowancesState = [];
      foreach ($shapeDef['sides'] as $sideKey => $sideLabel) {
        $savedSide = $meta['allowances'][$sideKey] ?? [];
        $allowancesState[$sideKey] = [
          'has_edge' => (bool) ($savedSide['has_edge'] ?? ($sideKey === 'front')),
          'edge_width' => (int) ($savedSide['edge_width'] ?? ($sideKey === 'front' ? 40 : 0)),
          'has_hem' => (bool) ($savedSide['has_hem'] ?? ($sideKey === 'front')),
          'hem_width' => (int) ($savedSide['hem_width'] ?? ($sideKey === 'front' ? 40 : 0)),
        ];
      }

      $shapesState[$shapeSlug] = [
        'is_active' => (bool) ($rec?->is_active ?? true),
        'length_min' => (int) ($meta['length_min'] ?? 300),
        'length_max' => (int) ($meta['length_max'] ?? 4000),
        'width_min' => (int) ($meta['width_min'] ?? 400),
        'width_max' => (int) ($meta['width_max'] ?? 1200),
        'allowances' => $allowancesState,
      ];
    }

    $uiMatrix = [];
    foreach ($this->getStoneInterfaceZones() as $zones) {
      foreach ($zones as $zoneKey => $info) {
        $recordMeta = $interfaceRecords->get($zoneKey)?->meta ?? [];
        $defaultUser = $info['default_user'] ?? true;

        $uiMatrix[$zoneKey] = [
          'userShow' => (bool) ($recordMeta['show_user'] ?? ($recordMeta['userShow'] ?? $defaultUser)),
          'managerShow' => (bool) ($recordMeta['show_manager'] ?? ($recordMeta['managerShow'] ?? true)),
          'adminShow' => (bool) ($recordMeta['show_admin'] ?? ($recordMeta['adminShow'] ?? true)),
        ];
      }
    }

    $slabLimitsMeta = $interfaceRecords->get('slab_limits')?->meta ?? [];

    return [
      'shapes' => $shapesState,
      'ui_matrix' => $uiMatrix,
      'trim_offset_default' => (int) ($slabLimitsMeta['trim_offset_default'] ?? 10),
      'max_transport_size_default' => (int) ($slabLimitsMeta['max_transport_size_default'] ?? 2800),
      'corner_add_length' => (int) ($slabLimitsMeta['corner_add_length'] ?? 920),
      'corner_add_width' => (int) ($slabLimitsMeta['corner_add_width'] ?? 760),
      'montage_rate_type' => (string) ($interfaceRecords->get('montage_rate_type')?->meta['value_user'] ?? 'per_linear_meter'),
    ];
  }

  protected function getStoneInterfaceZones(): array
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

  public function form(Schema $schema): Schema
  {
    $shapesTabs = [];

    foreach ($this->getStoneShapesRegistry() as $shapeSlug => $shapeDef) {
      $imageHtml = !empty($shapeDef['image'])
        ? "<div style=\"width: 260px; height: 160px; min-width: 260px; padding: 10px; border-radius: 12px; border: 1px solid rgba(156, 163, 175, 0.25); background: rgba(255, 255, 255, 0.95); display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);\">
            <img src=\"{$shapeDef['image']}\" style=\"max-width: 100%; max-height: 100%; object-fit: contain;\" alt=\"{$shapeDef['name']}\" />
           </div>"
        : "";

      $upperCode = strtoupper($shapeSlug);
      $visualHtml = "
        <div style=\"display: flex; align-items: center; gap: 18px; padding: 14px 18px; border-radius: 14px; border: 1px solid rgba(156, 163, 175, 0.25); background: rgba(243, 244, 246, 0.6); width: 100%; box-sizing: border-box;\">
          {$imageHtml}
          <div style=\"display: flex; flex-direction: column; gap: 6px; min-width: 0; flex: 1; padding-left: 4px;\">
            <span style=\"font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #0284c7; font-family: monospace;\">" . __('Code') . ": {$upperCode}</span>
            <h4 style=\"margin: 0; font-size: 16px; font-weight: 700; line-height: 1.3;\">{$shapeDef['name']}</h4>
            <p style=\"margin: 0; font-size: 13px; line-height: 1.4; color: #6b7280;\">{$shapeDef['desc']}</p>
          </div>
        </div>";

      $sideAllowancesInputs = [];
      foreach ($shapeDef['sides'] as $sideKey => $sideTitle) {
        $sideAllowancesInputs[] = Grid::make(12)->schema([
          TextEntry::make("shapes.{$shapeSlug}.allowances.{$sideKey}.title")
            ->hiddenLabel()
            ->columnSpan(4)
            ->state(fn () => new HtmlString("<div class='pt-2 text-xs font-semibold text-gray-800 dark:text-gray-200'>{$sideTitle}</div>")),

          Checkbox::make("shapes.{$shapeSlug}.allowances.{$sideKey}.has_edge")
            ->label(__('Edge'))
            ->columnSpan(2),

          TextInput::make("shapes.{$shapeSlug}.allowances.{$sideKey}.edge_width")
            ->label(__('Edge, mm'))
            ->numeric()
            ->default(40)
            ->columnSpan(2),

          Checkbox::make("shapes.{$shapeSlug}.allowances.{$sideKey}.has_hem")
            ->label(__('Hem'))
            ->columnSpan(2),

          TextInput::make("shapes.{$shapeSlug}.allowances.{$sideKey}.hem_width")
            ->label(__('Hem, mm'))
            ->numeric()
            ->default(40)
            ->columnSpan(2),
        ]);
      }

      $shapesTabs[] = Tab::make($shapeSlug)
        ->label($shapeDef['name'])
        ->schema([
          Section::make()
            ->schema([
              Grid::make(12)->schema([
                TextEntry::make("shapes.{$shapeSlug}.visual_card")
                  ->hiddenLabel()
                  ->columnSpan(['default' => 12, 'lg' => 7])
                  ->state(fn () => new HtmlString($visualHtml)),

                Grid::make(1)
                  ->columnSpan(['default' => 12, 'lg' => 5])
                  ->schema([
                    Toggle::make("shapes.{$shapeSlug}.is_active")
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
                TextInput::make("shapes.{$shapeSlug}.length_min")->label(__('Min Length (mm)'))->numeric()->required(),
                TextInput::make("shapes.{$shapeSlug}.length_max")->label(__('Max Length (mm)'))->numeric()->required(),
                TextInput::make("shapes.{$shapeSlug}.width_min")->label(__('Min Width (mm)'))->numeric()->required(),
                TextInput::make("shapes.{$shapeSlug}.width_max")->label(__('Max Width (mm)'))->numeric()->required(),
              ]),
            ]),

          Section::make(__('Side Allowances'))
            ->description(__('Sets automatic edge and hem allowances before sending to cutter'))
            ->schema($sideAllowancesInputs),
        ]);
    }

    return $schema
      ->statePath('data')
      ->components([
        Tabs::make('MasterStoneTabs')->tabs([
          Tab::make(__('Shapes and Allowances'))
            ->icon('heroicon-o-cube-transparent')
            ->schema([
              Tabs::make('ShapesInnerTabs')->tabs($shapesTabs),
            ]),

          Tab::make(__('Slab and Cutting Limits'))
            ->icon('heroicon-o-scissors')
            ->schema([
              Section::make(__('Slab Technological Offsets'))
                ->schema([
                  Grid::make(2)->schema([
                    TextInput::make('trim_offset_default')
                      ->label(__('Trim Offset, mm'))
                      ->helperText(__('Trim offset perimeter width subtracted before cutting'))
                      ->numeric()
                      ->required(),

                    TextInput::make('max_transport_size_default')
                      ->label(__('Max Transport Size, mm'))
                      ->helperText(__('Max indivisible part size. Larger parts will be cut on site'))
                      ->numeric()
                      ->required(),
                  ]),
                ]),

              Section::make(__('L-Joint Inner Corner Additions'))
                ->schema([
                  Grid::make(2)->schema([
                    TextInput::make('corner_add_length')
                      ->label(__('Inner Corner Add Length (mm)'))
                      ->numeric()
                      ->required(),

                    TextInput::make('corner_add_width')
                      ->label(__('Inner Corner Add Width (mm)'))
                      ->numeric()
                      ->required(),
                  ]),
                ]),
            ]),

          Tab::make(__('Interface Visibility (Permissions)'))
            ->icon('heroicon-o-eye')
            ->schema(
              collect($this->getStoneInterfaceZones())->map(function (array $zones, string $groupName) {
                return Section::make($groupName)
                  ->compact()
                  ->schema(
                    collect($zones)->map(function (array $info, string $key) {
                      return Grid::make(4)->schema([
                        Section::make($info['label'])->columnSpan(1)->compact(),
                        Checkbox::make("ui_matrix.{$key}.userShow")->label(__('User (Website)'))->columnSpan(1),
                        Checkbox::make("ui_matrix.{$key}.managerShow")->label(__('Manager (CRM)'))->columnSpan(1),
                        Checkbox::make("ui_matrix.{$key}.adminShow")->label(__('Administrator'))->columnSpan(1),
                      ]);
                    })->values()->toArray()
                  );
              })->values()->toArray()
            ),

          Tab::make(__('Services and Montage'))
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
            ]),
        ]),
      ]);
  }

  protected function getFormActions(): array
  {
    return [
      Action::make('save')
        ->label(__('Save changes'))
        ->submit('save'),
    ];
  }

  public function save(): void
  {
    $state = $this->form->getState();

    // 1. Сохранение форм и припусков в умный справочник stone_shapes_config
    /** @var ComplexDictionary $shapesDict */
    $shapesDict = ComplexDictionary::query()->firstOrCreate(
      ['code' => 'stone_shapes_config'],
      [
        'name' => ['ru' => __('Stone Shapes & Allowances Config'), 'en' => 'Stone Shapes & Allowances Config'],
        'is_active' => true,
      ]
    );

    foreach ($state['shapes'] ?? [] as $shapeSlug => $shapeData) {
      $registry = $this->getStoneShapesRegistry();
      $shapeTitle = $registry[$shapeSlug]['name'] ?? $shapeSlug;

      ComplexDictionaryRecord::query()->updateOrCreate(
        ['dictionary_id' => $shapesDict->id, 'slug' => $shapeSlug],
        [
          'name' => ['ru' => $shapeTitle, 'en' => $shapeTitle],
          'is_active' => (bool) ($shapeData['is_active'] ?? true),
          'meta' => [
            'length_min' => (int) ($shapeData['length_min'] ?? 300),
            'length_max' => (int) ($shapeData['length_max'] ?? 4000),
            'width_min' => (int) ($shapeData['width_min'] ?? 400),
            'width_max' => (int) ($shapeData['width_max'] ?? 1200),
            'allowances' => $shapeData['allowances'] ?? [],
          ],
        ]
      );
    }

    // 2. Сохранение параметров интерфейса и лимитов в stone_interface_settings
    /** @var ComplexDictionary $interfaceDict */
    $interfaceDict = ComplexDictionary::query()->firstOrCreate(
      ['code' => 'stone_interface_settings'],
      [
        'name' => ['ru' => __('Stone Interface Settings'), 'en' => 'Stone Interface Settings'],
        'is_active' => true,
      ]
    );

    // Сохранение лимитов слэба
    ComplexDictionaryRecord::query()->updateOrCreate(
      ['dictionary_id' => $interfaceDict->id, 'slug' => 'slab_limits'],
      [
        'name' => ['ru' => __('Slab technological limits'), 'en' => 'Slab technological limits'],
        'meta' => [
          'trim_offset_default' => (int) ($state['trim_offset_default'] ?? 10),
          'max_transport_size_default' => (int) ($state['max_transport_size_default'] ?? 2800),
          'corner_add_length' => (int) ($state['corner_add_length'] ?? 920),
          'corner_add_width' => (int) ($state['corner_add_width'] ?? 760),
        ],
        'is_active' => true,
      ]
    );

    // Сохранение ролевой матрицы
    foreach ($state['ui_matrix'] ?? [] as $zoneKey => $roles) {
      $userShow = (bool) ($roles['userShow'] ?? false);
      $managerShow = (bool) ($roles['managerShow'] ?? true);
      $adminShow = (bool) ($roles['adminShow'] ?? true);

      ComplexDictionaryRecord::query()->updateOrCreate(
        ['dictionary_id' => $interfaceDict->id, 'slug' => $zoneKey],
        [
          'name' => ['ru' => $zoneKey, 'en' => $zoneKey],
          'meta' => [
            'show_user' => $userShow,
            'show_manager' => $managerShow,
            'show_admin' => $adminShow,
            'userShow' => $userShow,
            'managerShow' => $managerShow,
            'adminShow' => $adminShow,
          ],
          'is_active' => true,
        ]
      );
    }

    // Режим тарификации монтажа
    ComplexDictionaryRecord::query()->updateOrCreate(
      ['dictionary_id' => $interfaceDict->id, 'slug' => 'montage_rate_type'],
      [
        'name' => ['ru' => __('Montage rate type'), 'en' => 'Montage rate type'],
        'meta' => [
          'value_user' => (string) ($state['montage_rate_type'] ?? 'per_linear_meter'),
          'value_manager' => (string) ($state['montage_rate_type'] ?? 'per_linear_meter'),
          'value_admin' => (string) ($state['montage_rate_type'] ?? 'per_linear_meter'),
        ],
        'is_active' => true,
      ]
    );

    // Инвалидация кэша каталога
    CatalogCache::invalidate();

    Notification::make()
      ->title(__('All shape parameters, allowances and slab limits saved successfully'))
      ->success()
      ->send();
  }
}