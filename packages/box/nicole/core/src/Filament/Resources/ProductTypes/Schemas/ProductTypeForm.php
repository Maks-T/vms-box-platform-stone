<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\ProductTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Nicole\Box\Core\Filament\Forms\Tabs\SalesChannelsTab;
use Nicole\Box\Core\Filament\Helpers\FormHelper;
use Nicole\Box\Core\Models\Attribute;
use Nicole\Box\Core\Models\ProductFamily;

class ProductTypeForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema->components([
      Tabs::make('ProductTypeTabs')
        ->tabs([

          Tabs\Tab::make(__('General Information'))
            ->icon('heroicon-o-information-circle')
            ->schema([
              Section::make()
                ->schema([
                  TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, $livewire) {
                      if ($livewire instanceof \Filament\Resources\Pages\CreateRecord) {
                        $set('code', \Illuminate\Support\Str::slug($state, '_'));
                        $set('slug', \Illuminate\Support\Str::slug($state, '-'));
                      }
                    })
                    ->translatable(),

                  TextInput::make('code')
                    ->label(__('Code'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->alphaDash(),

                  
                  TextInput::make('slug')
                    ->label(__('Slug'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->alphaDash()
                    ->helperText(__('Used for clean URLs (SEO)')),

                  Select::make('family_id')
                    ->label(__('Product Family'))
                    ->relationship('family', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                  TextInput::make('icon')
                    ->label(__('Icon'))
                    ->placeholder('heroicon-o-cube'),

                  Toggle::make('is_active')
                    ->label(__('Is Active'))
                    ->default(true)
                    ->columnSpanFull(),
                ])
                ->columns(2),
            ]),

          Tabs\Tab::make(__('Technical Specifications'))
            ->icon('heroicon-o-adjustments-vertical')
            ->visible(function (Get $get) {
              $familyId = $get('family_id');
              
              return $familyId && ProductFamily::find($familyId)?->meta_schema;
            })
            ->schema(function (Get $get) {
              $familyId = $get('family_id');
              
              $schema = ProductFamily::find($familyId)?->meta_schema ?? [];
              $components = [];
              $locale = app()->getLocale();

              foreach ($schema as $field) {
                
                $key = "meta.{$field['key']}";
                $label = is_array($field['label']) ? ($field['label'][$locale] ?? $field['key']) : $field['label'];

                $input = match ($field['type']) {
                  'boolean' => Toggle::make($key)->inline(false),
                  'number' => TextInput::make($key)->numeric(),
                  default => TextInput::make($key),
                };

                $components[] = $input->label($label)->columnSpan($field['width'] ?? 1);
              }

              return [
                Section::make(__('Family Parameters'))
                  ->schema($components)
                  ->columns(2)
              ];
            }),


          Tabs\Tab::make(__('Pricing Settings'))
            ->icon('heroicon-o-banknotes')
            ->schema([
              Section::make()->schema([
                Select::make('pricing_mode')
                  ->label(__('Pricing Mode'))
                  ->options([
                    'manual' => __('Manual (in product card)'),
                    'complex_dictionary' => __('From Complex Dictionary'),
                  ])
                  ->default('manual')
                  ->live()
                  ->required(),

                Grid::make(2)
                  ->visible(fn(Get $get) => $get('pricing_mode') === 'complex_dictionary')
                  ->schema([

                    Select::make('pricing_attribute_id')
                      ->label(__('Mapping Attribute'))
                      ->options(function () {
                        return Attribute::where('type', Attribute::TYPE_COMPLEX)
                          ->with('complexDictionary')
                          ->get()
                          ->filter(function ($attr) {
                            
                            $schema = $attr->complexDictionary?->meta_schema ?? [];
                            foreach ($schema as $field) {
                              if (($field['type'] ?? '') === 'price') return true;
                            }
                            return false;
                          })
                          ->pluck('name', 'id');
                      })
                      ->live()
                      ->afterStateUpdated(fn(Set $set) => $set('pricing_field', null))
                      ->required(),

                    Select::make('pricing_field')
                      ->label(__('Dictionary Field Key'))
                      ->options(function (Get $get) {
                        $attrId = $get('pricing_attribute_id');
                        if (!$attrId) return [];

                        $attr = Attribute::with('complexDictionary')->find($attrId);
                        
                        $schema = $attr?->complexDictionary?->meta_schema ?? [];

                        $options = [];
                        $locale = app()->getLocale();
                        foreach ($schema as $field) {
                          if (($field['type'] ?? '') === 'price') {
                            $label = is_array($field['label']) ? ($field['label'][$locale] ?? $field['key']) : ($field['label'] ?? $field['key']);
                            $options[$field['key']] = $label . " ({$field['key']})";
                          }
                        }
                        return $options;
                      })
                      ->required(),
                  ]),
              ]),
            ]),

          SalesChannelsTab::make('product_type'),
        ])
        ->columnSpanFull(),
    ]);
  }
}
