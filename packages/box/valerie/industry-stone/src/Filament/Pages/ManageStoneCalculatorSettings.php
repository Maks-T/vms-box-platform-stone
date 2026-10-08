<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Filament\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Nicole\Box\Core\Models\ComplexDictionary;
use Nicole\Box\Core\Models\ComplexDictionaryRecord;
use Nicole\Box\Core\Models\ProductType;
use Nicole\Box\Core\Support\CatalogCache;

/**
 * Единый оркестратор настроек фабрики камнеобработки:
 * геометрия форм, профили припусков, физика слэбов типов камня и матрица прав.
 *
 * @property Schema $form
 * @since 2026-10-08
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
   * Реестр геометрических форм изделий из камня.
   *
   * @return array<string, array{name: string, desc: string, blueprint: string}>
   */
  protected function getStoneShapesRegistry(): array
  {
    return [
      'worktop_line'      => [
        'name'      => __('Straight Worktop'),
        'desc'      => __('Straight worktop for kitchen or bathroom along a single wall'),
        'blueprint' => asset('pdf/layouts/worktop/line.png'),
      ],
      'worktop_l_shaped'  => [
        'name'      => __('L-Shaped Worktop'),
        'desc'      => __('Corner construction of two joined wings with a seam'),
        'blueprint' => asset('pdf/layouts/worktop/l_shaped.png'),
      ],
      'worktop_u_shaped'  => [
        'name'      => __('U-Shaped Worktop'),
        'desc'      => __('Three-section construction with two corner joints'),
        'blueprint' => asset('pdf/layouts/worktop/u_shaped.png'),
      ],
      'island'            => [
        'name'      => __('Kitchen Island'),
        'desc'      => __('Freestanding island module with perimeter processing'),
        'blueprint' => asset('pdf/layouts/worktop/line.png'),
      ],
      'bar_counter'       => [
        'name'      => __('Bar Counter'),
        'desc'      => __('Narrow cantilever or wall panel with front edge'),
        'blueprint' => asset('pdf/layouts/worktop/line.png'),
      ],
      'windowsill_line'   => [
        'name'      => __('Straight Windowsill'),
        'desc'      => __('Rectangular windowsill with front drip edge and ears'),
        'blueprint' => asset('pdf/layouts/windowsill/line.png'),
      ],
      'windowsill_corner' => [
        'name'      => __('Corner Windowsill'),
        'desc'      => __('Corner windowsill for 90 degree bay or balcony unit'),
        'blueprint' => asset('pdf/layouts/windowsill/corner.png'),
      ],
      'windowsill_bay'    => [
        'name'      => __('Bay Windowsill'),
        'desc'      => __('Multi-section bay windowsill with obtuse corner joints'),
        'blueprint' => asset('pdf/layouts/windowsill/bay.png'),
      ],
      'wall_panel'        => [
        'name'      => __('Wall Panel (Backsplash)'),
        'desc'      => __('Vertical wall panel between worktop and upper cabinets'),
        'blueprint' => asset('pdf/layouts/worktop/line.png'),
      ],
    ];
  }

  /**
   * Реестр технологических профилей припусков.
   *
   * @return array<string, array{name: string, desc: string, default_edge: int, default_hem: int}>
   */
  protected function getAllowanceProfilesRegistry(): array
  {
    return [
      'acrylic_standard' => [
        'name'         => __('Acrylic Stone Profile'),
        'desc'         => __('Standard allowances for acrylic solid surface with glued hem (+40mm)'),
        'default_edge' => 40,
        'default_hem'  => 40,
      ],
      'quartz_standard'  => [
        'name'         => __('Quartz Composite Profile'),
        'desc'         => __('Standard allowances for engineered quartz (no hem, 40mm miter/edge)'),
        'default_edge' => 40,
        'default_hem'  => 0,
      ],
      'natural_stone'    => [
        'name'         => __('Natural Stone Profile'),
        'desc'         => __('Allowances for marble and granite slabs with polished edge'),
        'default_edge' => 30,
        'default_hem'  => 0,
      ],
    ];
  }

  /**
   * Стандартные стороны для настройки припусков.
   *
   * @return array<string, string>
   */
  protected function getStandardSides(): array
  {
    return [
      'front' => __('Front (Visible edge)'),
      'left'  => __('Left End'),
      'right' => __('Right End'),
      'back'  => __('Back (Wall side)'),
      'ears'  => __('Ears / Side reveals'),
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

    /** @var ComplexDictionary|null $profilesDict */
    $profilesDict = ComplexDictionary::query()
      ->where('code', 'stone_allowance_profiles')
      ->with('records')
      ->first();
    $profileRecords = $profilesDict?->records?->keyBy('slug') ?? collect();

    /** @var ComplexDictionary|null $interfaceDict */
    $interfaceDict = ComplexDictionary::query()
      ->where('code', 'stone_interface_settings')
      ->with('records')
      ->first();
    $interfaceRecords = $interfaceDict?->records?->keyBy('slug') ?? collect();

    // 1. Формы
    $shapesState = [];
    foreach ($this->getStoneShapesRegistry() as $shapeSlug => $shapeDef) {
      $rec = $shapeRecords->get($shapeSlug);
      $meta = $rec?->meta ?? [];
      $shapesState[$shapeSlug] = [
        'is_active'  => (bool)($rec?->is_active ?? true),
        'length_min' => (int)($meta['length_min'] ?? 300),
        'length_max' => (int)($meta['length_max'] ?? 4000),
        'width_min'  => (int)($meta['width_min'] ?? 400),
        'width_max'  => (int)($meta['width_max'] ?? 1200),
      ];
    }

    // 2. Профили припусков
    $profilesState = [];
    foreach ($this->getAllowanceProfilesRegistry() as $profileSlug => $profileDef) {
      $rec = $profileRecords->get($profileSlug);
      $meta = $rec?->meta ?? [];
      $sidesState = [];
      foreach ($this->getStandardSides() as $sideKey => $sideLabel) {
        $savedSide = $meta['sides'][$sideKey] ?? [];
        $isFront = $sideKey === 'front';
        $sidesState[$sideKey] = [
          'has_edge'   => (bool)($savedSide['has_edge'] ?? $isFront),
          'edge_width' => (int)($savedSide['edge_width'] ?? ($isFront ? $profileDef['default_edge'] : 0)),
          'has_hem'    => (bool)($savedSide['has_hem'] ?? ($isFront && $profileDef['default_hem'] > 0)),
          'hem_width'  => (int)($savedSide['hem_width'] ?? ($isFront ? $profileDef['default_hem'] : 0)),
        ];
      }
      $profilesState[$profileSlug] = [
        'sides' => $sidesState,
      ];
    }

    // 3. Типы камня (из ProductType)
    $stoneTypes = ProductType::query()
      ->whereHas('family', fn($q) => $q->whereIn('code', ['stone', 'natural-stone']))
      ->get();
    $materialsState = [];
    foreach ($stoneTypes as $type) {
      $meta = $type->meta ?? [];
      $isQuartz = str_contains($type->code, 'quartz');
      $materialsState[$type->code] = [
        'step'               => (float)($meta['step'] ?? ($isQuartz ? 1.0 : 0.5)),
        'minPart'            => (int)($meta['minPart'] ?? ($isQuartz ? 20 : 12)),
        'axisX'              => (bool)($meta['axisX'] ?? !$isQuartz),
        'maxStack'           => (int)($meta['maxStack'] ?? 1),
        'is_separate'        => (bool)($meta['is_separate'] ?? $isQuartz),
        'allow_rounding'     => (bool)($meta['allow_rounding'] ?? !$isQuartz),
        'trim_offset'        => (int)($meta['trim_offset'] ?? ($isQuartz ? 15 : 10)),
        'max_transport_size' => (int)($meta['max_transport_size'] ?? 2800),
        'corner_add_length'  => (int)($meta['corner_add_length'] ?? ($isQuartz ? 750 : 920)),
        'corner_add_width'   => (int)($meta['corner_add_width'] ?? ($isQuartz ? 700 : 760)),
        'allowance_profile'  => (string)($meta['allowance_profile'] ?? ($isQuartz ? 'quartz_standard' : 'acrylic_standard')),
      ];
    }

    // 4. Права UI
    $uiMatrix = [];
    foreach ($this->getStoneInterfaceZones() as $zones) {
      foreach ($zones as $zoneKey => $info) {
        $recordMeta = $interfaceRecords->get($zoneKey)?->meta ?? [];
        $defaultUser = $info['default_user'] ?? true;
        $uiMatrix[$zoneKey] = [
          'userShow'    => (bool)($recordMeta['show_user'] ?? ($recordMeta['userShow'] ?? $defaultUser)),
          'managerShow' => (bool)($recordMeta['show_manager'] ?? ($recordMeta['managerShow'] ?? true)),
          'adminShow'   => (bool)($recordMeta['show_admin'] ?? ($recordMeta['adminShow'] ?? true)),
        ];
      }
    }

    return [
      'shapes'            => $shapesState,
      'profiles'          => $profilesState,
      'materials'         => $materialsState,
      'ui_matrix'         => $uiMatrix,
      'montage_rate_type' => (string)($interfaceRecords->get('montage_rate_type')?->meta['value_user'] ?? 'per_linear_meter'),
    ];
  }

  protected function getStoneInterfaceZones(): array
  {
    return [
      __('Pricing and Estimate')             => [
        'price_materials' => ['label' => __('Display retail slab price in catalog'), 'default_user' => true],
        'price_breakdown' => ['label' => __('Cost breakdown card (Materials, Works, Montage)'), 'default_user' => false],
        'estimate_table'  => ['label' => __('Detailed estimate specification table'), 'default_user' => false],
        'total_amount'    => ['label' => __('Total order amount bar'), 'default_user' => true],
      ],
      __('Additional Manufacturing Options') => [
        'show_sinks_step'       => ['label' => __('Sink and faucet selection step'), 'default_user' => true],
        'show_wall_panel_step'  => ['label' => __('Wall panel (backsplash) selection step'), 'default_user' => true],
        'show_bar_counter_step' => ['label' => __('Bar counter / island selection step'), 'default_user' => true],
        'show_edge_step'        => ['label' => __('Decorative edge profile selection step'), 'default_user' => true],
      ],
      __('Export and Action Buttons')        => [
        'btn_download_pdf' => ['label' => __('Download PDF quote button'), 'default_user' => false],
        'btn_print_html'   => ['label' => __('Print quote HTML button'), 'default_user' => false],
        'btn_share_link'   => ['label' => __('Share calculation link button'), 'default_user' => true],
      ],
    ];
  }

  public function form(Schema $schema): Schema
  {
    // Вкладка 1: Конструкции
    $shapesTabs = [];
    foreach ($this->getStoneShapesRegistry() as $shapeSlug => $shapeDef) {
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
                ->state(fn() => new HtmlString($visualHtml)),

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
        ]);
    }

    // Вкладка 2: Профили припусков
    $profilesTabs = [];
    foreach ($this->getAllowanceProfilesRegistry() as $profileSlug => $profileDef) {
      $sideInputs = [];
      foreach ($this->getStandardSides() as $sideKey => $sideTitle) {
        $sideInputs[] = Grid::make(12)->schema([
          TextEntry::make('profiles.' . $profileSlug . '.sides.' . $sideKey . '.title')
            ->hiddenLabel()
            ->columnSpan(4)
            ->state(fn() => new HtmlString("<div class='pt-2 text-xs font-semibold text-gray-800 dark:text-gray-200'>{$sideTitle}</div>")),

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

    // Вкладка 3: Материалы (типы камня)
    $stoneTypes = ProductType::query()
      ->whereHas('family', fn($q) => $q->whereIn('code', ['stone', 'natural-stone']))
      ->get();
    $materialsTabs = [];
    $profileOptions = collect($this->getAllowanceProfilesRegistry())->mapWithKeys(
      fn($item, $key) => [$key => $item['name']]
    )->toArray();

    foreach ($stoneTypes as $type) {
      $typeName = $type->getTranslation('name', app()->getLocale()) ?: $type->name;

      $materialsTabs[] = Tab::make($type->code)
        ->label($typeName)
        ->schema([
          Section::make(__('Cutting Physics & Sales Step'))
            ->schema([
              Grid::make(3)->schema([
                TextInput::make('materials.' . $type->code . '.step')
                  ->label(__('Sale Step (0.25 / 0.5 / 1.0)'))
                  ->numeric()
                  ->required(),

                Radio::make('materials.' . $type->code . '.axisX')
                  ->label(__('Cutting Axis Direction'))
                  ->options([
                    1 => __('Along length (Axis X)'),
                    0 => __('Across width (Axis Y)'),
                  ])
                  ->inline(),

                TextInput::make('materials.' . $type->code . '.minPart')
                  ->label(__('Min Part Size (mm)'))
                  ->numeric()
                  ->required(),
              ]),

              Grid::make(3)->schema([
                TextInput::make('materials.' . $type->code . '.maxStack')
                  ->label(__('Max Stack'))
                  ->numeric()
                  ->default(1),

                Toggle::make('materials.' . $type->code . '.allow_rounding')
                  ->label(__('Allow Corner Roundings (R1-R8)'))
                  ->inline(false),

                Toggle::make('materials.' . $type->code . '.is_separate')
                  ->label(__('Cut Separately from Wall Panel'))
                  ->inline(false),
              ]),
            ]),

          Section::make(__('Technological Allowances & Transport'))
            ->schema([
              Grid::make(2)->schema([
                TextInput::make('materials.' . $type->code . '.trim_offset')
                  ->label(__('Trim Offset (mm)'))
                  ->helperText(__('Perimeter margin cut from raw factory slab'))
                  ->numeric()
                  ->required(),

                TextInput::make('materials.' . $type->code . '.max_transport_size')
                  ->label(__('Max Transport Size (mm)'))
                  ->helperText(__('Indivisible part length limit. Exceeding parts will be cut on site.'))
                  ->numeric()
                  ->required(),
              ]),

              Grid::make(2)->schema([
                TextInput::make('materials.' . $type->code . '.corner_add_length')
                  ->label(__('Inner Corner Add Length (mm)'))
                  ->numeric()
                  ->required(),

                TextInput::make('materials.' . $type->code . '.corner_add_width')
                  ->label(__('Inner Corner Add Width (mm)'))
                  ->numeric()
                  ->required(),
              ]),

              Select::make('materials.' . $type->code . '.allowance_profile')
                ->label(__('Assigned Allowance Profile'))
                ->options($profileOptions)
                ->required()
                ->native(false),
            ]),
        ]);
    }

    return $schema
      ->statePath('data')
      ->components([
        Tabs::make('MasterStoneTabs')->tabs([
          Tab::make(__('Shapes and Limits'))
            ->icon('heroicon-o-cube-transparent')
            ->schema([
              Tabs::make('ShapesInnerTabs')->tabs($shapesTabs),
            ]),

          Tab::make(__('Allowance Profiles'))
            ->icon('heroicon-o-swatch')
            ->schema([
              Tabs::make('ProfilesInnerTabs')->tabs($profilesTabs),
            ]),

          Tab::make(__('Materials & Slabs'))
            ->icon('heroicon-o-scissors')
            ->schema([
              Tabs::make('MaterialsInnerTabs')->tabs($materialsTabs),
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
                        Checkbox::make('ui_matrix.' . $key . '.userShow')->label(__('User (Website)'))->columnSpan(1),
                        Checkbox::make('ui_matrix.' . $key . '.managerShow')->label(__('Manager (CRM)'))->columnSpan(1),
                        Checkbox::make('ui_matrix.' . $key . '.adminShow')->label(__('Administrator'))->columnSpan(1),
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
                      'per_sq_meter'     => __('Per square meter of area'),
                      'fixed'            => __('Fixed rate per set'),
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

    // 1. Формы: stone_shapes_config
    /** @var ComplexDictionary $shapesDict */
    $shapesDict = ComplexDictionary::query()->firstOrCreate(
      ['code' => 'stone_shapes_config'],
      [
        'name'      => ['ru' => __('Stone Shapes & Allowances Config'), 'en' => 'Stone Shapes & Allowances Config'],
        'is_active' => true,
      ]
    );

    foreach ($state['shapes'] ?? [] as $shapeSlug => $shapeData) {
      $registry = $this->getStoneShapesRegistry();
      $shapeTitle = $registry[$shapeSlug]['name'] ?? $shapeSlug;

      ComplexDictionaryRecord::query()->updateOrCreate(
        ['dictionary_id' => $shapesDict->id, 'slug' => $shapeSlug],
        [
          'name'      => ['ru' => $shapeTitle, 'en' => $shapeTitle],
          'is_active' => (bool)($shapeData['is_active'] ?? true),
          'meta'      => [
            'length_min' => (int)($shapeData['length_min'] ?? 300),
            'length_max' => (int)($shapeData['length_max'] ?? 4000),
            'width_min'  => (int)($shapeData['width_min'] ?? 400),
            'width_max'  => (int)($shapeData['width_max'] ?? 1200),
          ],
        ]
      );
    }

    // 2. Профили припусков: stone_allowance_profiles
    /** @var ComplexDictionary $profilesDict */
    $profilesDict = ComplexDictionary::query()->firstOrCreate(
      ['code' => 'stone_allowance_profiles'],
      [
        'name'      => ['ru' => 'Профили припусков камня', 'en' => 'Stone Allowance Profiles'],
        'is_active' => true,
      ]
    );

    foreach ($state['profiles'] ?? [] as $profileSlug => $profileData) {
      $registry = $this->getAllowanceProfilesRegistry();
      $profileTitle = $registry[$profileSlug]['name'] ?? $profileSlug;

      ComplexDictionaryRecord::query()->updateOrCreate(
        ['dictionary_id' => $profilesDict->id, 'slug' => $profileSlug],
        [
          'name'      => ['ru' => $profileTitle, 'en' => $profileTitle],
          'is_active' => true,
          'meta'      => [
            'sides' => $profileData['sides'] ?? [],
          ],
        ]
      );
    }

    // 3. Материалы: сохранение напрямую в ProductType.meta (устранение дублирования)
    foreach ($state['materials'] ?? [] as $typeCode => $matData) {
      $type = ProductType::query()->where('code', $typeCode)->first();
      if ($type) {
        $currentMeta = $type->meta ?? [];
        $type->update([
          'meta' => array_merge($currentMeta, [
            'step'               => (float)($matData['step'] ?? 0.5),
            'minPart'            => (int)($matData['minPart'] ?? 12),
            'axisX'              => (bool)($matData['axisX'] ?? true),
            'maxStack'           => (int)($matData['maxStack'] ?? 1),
            'allow_rounding'     => (bool)($matData['allow_rounding'] ?? true),
            'is_separate'        => (bool)($matData['is_separate'] ?? false),
            'trim_offset'        => (int)($matData['trim_offset'] ?? 10),
            'max_transport_size' => (int)($matData['max_transport_size'] ?? 2800),
            'corner_add_length'  => (int)($matData['corner_add_length'] ?? 920),
            'corner_add_width'   => (int)($matData['corner_add_width'] ?? 760),
            'allowance_profile'  => (string)($matData['allowance_profile'] ?? 'acrylic_standard'),
          ]),
        ]);
      }
    }

    // 4. Права интерфейса: stone_interface_settings
    /** @var ComplexDictionary $interfaceDict */
    $interfaceDict = ComplexDictionary::query()->firstOrCreate(
      ['code' => 'stone_interface_settings'],
      [
        'name'      => ['ru' => __('Stone Interface Settings'), 'en' => 'Stone Interface Settings'],
        'is_active' => true,
      ]
    );

    foreach ($state['ui_matrix'] ?? [] as $zoneKey => $roles) {
      $userShow = (bool)($roles['userShow'] ?? false);
      $managerShow = (bool)($roles['managerShow'] ?? true);
      $adminShow = (bool)($roles['adminShow'] ?? true);

      ComplexDictionaryRecord::query()->updateOrCreate(
        ['dictionary_id' => $interfaceDict->id, 'slug' => $zoneKey],
        [
          'name'      => ['ru' => $zoneKey, 'en' => $zoneKey],
          'meta'      => [
            'show_user'    => $userShow,
            'show_manager' => $managerShow,
            'show_admin'   => $adminShow,
            'userShow'     => $userShow,
            'managerShow'  => $managerShow,
            'adminShow'    => $adminShow,
          ],
          'is_active' => true,
        ]
      );
    }

    // Режим тарификации монтажа
    ComplexDictionaryRecord::query()->updateOrCreate(
      ['dictionary_id' => $interfaceDict->id, 'slug' => 'montage_rate_type'],
      [
        'name'      => ['ru' => __('Montage rate type'), 'en' => 'Montage rate type'],
        'meta'      => [
          'value_user'    => (string)($state['montage_rate_type'] ?? 'per_linear_meter'),
          'value_manager' => (string)($state['montage_rate_type'] ?? 'per_linear_meter'),
          'value_admin'   => (string)($state['montage_rate_type'] ?? 'per_linear_meter'),
        ],
        'is_active' => true,
      ]
    );

    CatalogCache::invalidate();

    Notification::make()
      ->title(__('All shape parameters, allowances and slab limits saved successfully'))
      ->success()
      ->send();
  }
}
