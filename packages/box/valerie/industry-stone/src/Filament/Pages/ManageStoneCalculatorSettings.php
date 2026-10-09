<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Filament\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Nicole\Box\Core\Models\ComplexDictionary;
use Nicole\Box\Core\Models\ComplexDictionaryRecord;
use Nicole\Box\Core\Models\ProductType;
use Nicole\Box\Core\Support\CatalogCache;
use Valerie\Box\IndustryStone\Filament\Pages\StoneSettings\Tabs\AllowanceProfilesTab;
use Valerie\Box\IndustryStone\Filament\Pages\StoneSettings\Tabs\MaterialsTab;
use Valerie\Box\IndustryStone\Filament\Pages\StoneSettings\Tabs\PermissionsTab;
use Valerie\Box\IndustryStone\Filament\Pages\StoneSettings\Tabs\ServicesTab;
use Valerie\Box\IndustryStone\Filament\Pages\StoneSettings\Tabs\ShapesTab;

/**
 * Единый модульный оркестратор настроек фабрики камнеобработки:
 * геометрия форм, профили припусков, физика слэбов и матрица прав.
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
   * Кэшированная выборка типов камня для текущего запроса.
   *
   * @return Collection<int, ProductType>
   */
  protected function getStoneTypes(): Collection
  {
    return once(fn () => ProductType::query()
      ->whereHas('family', fn ($q) => $q->whereIn('code', ['stone', 'natural-stone']))
      ->get()
    );
  }

  public function form(Schema $schema): Schema
  {
    $profileOptions = collect(AllowanceProfilesTab::getRegistry())->mapWithKeys(
      fn ($item, $key) => [$key => $item['name']]
    )->toArray();
    $stoneTypes = $this->getStoneTypes();

    return $schema
      ->statePath('data')
      ->components([
        Tabs::make('MasterStoneTabs')->tabs([
          ShapesTab::make(),
          AllowanceProfilesTab::make(),
          MaterialsTab::make($stoneTypes, $profileOptions),
          PermissionsTab::make(),
          ServicesTab::make(),
        ]),
      ]);
  }

  protected function loadCurrentSettings(): array
  {
    $dictionaries = ComplexDictionary::query()
      ->whereIn('code', ['stone_shapes', 'stone_allowance_profiles', 'stone_interface_settings'])
      ->with('records')
      ->get()
      ->keyBy('code');

    $shapeRecords = $dictionaries->get('stone_shapes')?->records?->keyBy('slug') ?? collect();
    $profileRecords = $dictionaries->get('stone_allowance_profiles')?->records?->keyBy('slug') ?? collect();
    $interfaceRecords = $dictionaries->get('stone_interface_settings')?->records?->keyBy('slug') ?? collect();

    $shapesState = [];
    foreach (ShapesTab::getRegistry() as $shapeSlug => $shapeDef) {
      $rec = $shapeRecords->get($shapeSlug);
      $meta = $rec?->meta ?? [];
      $rawServices = $meta['allowed_services'] ?? [];
      $allowedServices = is_array($rawServices)
        ? $rawServices
        : array_values(array_filter(array_map('trim', explode(',', (string) $rawServices))));

      $shapesState[$shapeSlug] = [
        'is_active' => (bool) ($rec?->is_active ?? true),
        'length_min' => (int) ($meta['length_min'] ?? 300),
        'length_max' => (int) ($meta['length_max'] ?? 4000),
        'width_min' => (int) ($meta['width_min'] ?? 400),
        'width_max' => (int) ($meta['width_max'] ?? 1200),
        'geometry_type' => (string) ($meta['geometry_type'] ?? 'line'),
        'category_scope' => (string) ($meta['category_scope'] ?? 'kitchen'),
        'allowed_services' => $allowedServices,
      ];
    }

    $profilesState = [];
    foreach (AllowanceProfilesTab::getRegistry() as $profileSlug => $profileDef) {
      $rec = $profileRecords->get($profileSlug);
      $meta = $rec?->meta ?? [];
      $sidesState = [];
      foreach (AllowanceProfilesTab::getStandardSides() as $sideKey => $sideLabel) {
        $savedSide = $meta['sides'][$sideKey] ?? [];
        $isFront = $sideKey === 'front';
        $sidesState[$sideKey] = [
          'has_edge' => (bool) ($savedSide['has_edge'] ?? $isFront),
          'edge_width' => (int) ($savedSide['edge_width'] ?? ($isFront ? $profileDef['default_edge'] : 0)),
          'has_hem' => (bool) ($savedSide['has_hem'] ?? ($isFront && $profileDef['default_hem'] > 0)),
          'hem_width' => (int) ($savedSide['hem_width'] ?? ($isFront ? $profileDef['default_hem'] : 0)),
        ];
      }
      $profilesState[$profileSlug] = [
        'sides' => $sidesState,
      ];
    }

    // 3. Загрузка физики слэбов напрямую из ProductType
    $stoneTypes = $this->getStoneTypes();
    $materialsState = [];
    foreach ($stoneTypes as $type) {
      $meta = $type->meta ?? [];
      $isQuartz = str_contains($type->code, 'quartz');
      $materialsState[$type->code] = [
        'step' => (float) ($meta['step'] ?? ($isQuartz ? 1.0 : 0.5)),
        'minPart' => (int) ($meta['minPart'] ?? ($isQuartz ? 20 : 12)),
        'axisX' => (bool) ($meta['axisX'] ?? !$isQuartz),
        'maxStack' => (int) ($meta['maxStack'] ?? 1),
        'is_separate' => (bool) ($meta['is_separate'] ?? $isQuartz),
        'allow_rounding' => (bool) ($meta['allow_rounding'] ?? !$isQuartz),
        'trim_offset' => (int) ($meta['trim_offset'] ?? ($isQuartz ? 15 : 10)),
        'max_transport_size' => (int) ($meta['max_transport_size'] ?? 2800),
        'corner_add_length' => (int) ($meta['corner_add_length'] ?? ($isQuartz ? 750 : 920)),
        'corner_add_width' => (int) ($meta['corner_add_width'] ?? ($isQuartz ? 700 : 760)),
        'allowance_profile' => (string) ($meta['allowance_profile'] ?? ($isQuartz ? 'quartz_standard' : 'acrylic_standard')),
      ];
    }

    // 4. Загрузка матрицы прав интерфейса
    $uiMatrix = [];
    foreach (PermissionsTab::getZones() as $zones) {
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

    return [
      'shapes' => $shapesState,
      'profiles' => $profilesState,
      'materials' => $materialsState,
      'ui_matrix' => $uiMatrix,
      'montage_rate_type' => (string) ($interfaceRecords->get('montage_rate_type')?->meta['value_user'] ?? 'per_linear_meter'),
    ];
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
    DB::transaction(function () {
    $state = $this->form->getState();

    // 1. Сохранение форм в stone_shapes
    /** @var ComplexDictionary $shapesDict */
    $shapesDict = ComplexDictionary::query()->firstOrCreate(
      ['code' => 'stone_shapes'],
      [
        'name' => ['ru' => 'Формы каменных изделий', 'en' => 'Stone Product Shapes'],
        'is_active' => true,
      ]
    );

    foreach ($state['shapes'] ?? [] as $shapeSlug => $shapeData) {
      $registry = ShapesTab::getRegistry();
      $shapeTitle = $registry[$shapeSlug]['name'] ?? $shapeSlug;
      $allowedServices = is_array($shapeData['allowed_services'] ?? null)
        ? array_values(array_filter($shapeData['allowed_services']))
        : array_values(array_filter(array_map('trim', explode(',', (string) ($shapeData['allowed_services'] ?? '')))));

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
            'geometry_type' => (string) ($shapeData['geometry_type'] ?? 'line'),
            'category_scope' => (string) ($shapeData['category_scope'] ?? 'kitchen'),
            'allowed_services' => $allowedServices,
          ],
        ]
      );
    }

    // 2. Сохранение профилей припусков в stone_allowance_profiles
    /** @var ComplexDictionary $profilesDict */
    $profilesDict = ComplexDictionary::query()->firstOrCreate(
      ['code' => 'stone_allowance_profiles'],
      [
        'name' => ['ru' => 'Профили припусков камня', 'en' => 'Stone Allowance Profiles'],
        'is_active' => true,
      ]
    );

    foreach ($state['profiles'] ?? [] as $profileSlug => $profileData) {
      $registry = AllowanceProfilesTab::getRegistry();
      $profileTitle = $registry[$profileSlug]['name'] ?? $profileSlug;

      ComplexDictionaryRecord::query()->updateOrCreate(
        ['dictionary_id' => $profilesDict->id, 'slug' => $profileSlug],
        [
          'name' => ['ru' => $profileTitle, 'en' => $profileTitle],
          'is_active' => true,
          'meta' => [
            'sides' => $profileData['sides'] ?? [],
          ],
        ]
      );
    }

    // 3. Сохранение физики слэбов напрямую в ProductType.meta
    foreach ($state['materials'] ?? [] as $typeCode => $matData) {
      $type = ProductType::query()->where('code', $typeCode)->first();
      if ($type) {
        $currentMeta = $type->meta ?? [];
        $type->update([
          'meta' => array_merge($currentMeta, [
            'step' => (float) ($matData['step'] ?? 0.5),
            'minPart' => (int) ($matData['minPart'] ?? 12),
            'axisX' => (bool) ($matData['axisX'] ?? true),
            'maxStack' => (int) ($matData['maxStack'] ?? 1),
            'allow_rounding' => (bool) ($matData['allow_rounding'] ?? true),
            'is_separate' => (bool) ($matData['is_separate'] ?? false),
            'trim_offset' => (int) ($matData['trim_offset'] ?? 10),
            'max_transport_size' => (int) ($matData['max_transport_size'] ?? 2800),
            'corner_add_length' => (int) ($matData['corner_add_length'] ?? 920),
            'corner_add_width' => (int) ($matData['corner_add_width'] ?? 760),
            'allowance_profile' => (string) ($matData['allowance_profile'] ?? 'acrylic_standard'),
          ]),
        ]);
      }
    }

    // 4. Сохранение прав интерфейса в stone_interface_settings
    /** @var ComplexDictionary $interfaceDict */
    $interfaceDict = ComplexDictionary::query()->firstOrCreate(
      ['code' => 'stone_interface_settings'],
      [
        'name' => ['ru' => 'Настройки интерфейса и слэбов камня', 'en' => 'Stone Interface Settings'],
        'is_active' => true,
      ]
    );

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
        'name' => ['ru' => 'Принцип тарификации монтажа', 'en' => 'Montage rate type'],
        'meta' => [
          'value_user' => (string) ($state['montage_rate_type'] ?? 'per_linear_meter'),
          'value_manager' => (string) ($state['montage_rate_type'] ?? 'per_linear_meter'),
          'value_admin' => (string) ($state['montage_rate_type'] ?? 'per_linear_meter'),
        ],
        'is_active' => true,
      ]
    );
    });

    CatalogCache::invalidate();

    Notification::make()
      ->title(__('All shape parameters, allowances and slab limits saved successfully'))
      ->success()
      ->send();
  }
}
