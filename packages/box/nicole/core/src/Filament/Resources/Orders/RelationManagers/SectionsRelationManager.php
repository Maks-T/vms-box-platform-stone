<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\Action; 
use Filament\Actions\DeleteAction; 
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry; 
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Nicole\Box\Core\Models\OrderSection;

class SectionsRelationManager extends RelationManager
{
  protected static string $relationship = 'sections';

  public static function getTitle(Model $ownerRecord, string $pageClass): string
  {
    return __('Order Sections'); 
  }

  /**
   * Сигнатура Filament v5 [OrderStatusResource.php]
   */
  public function form(Schema $schema): Schema
  {
    return $schema->components([
      TextInput::make('title')
        ->label(__('Name'))
        ->required()
        ->columnSpanFull(),
      TextInput::make('total_price')
        ->label(__('Total'))
        ->numeric()
        ->required(),
    ]);
  }

  /**
   * Таблица изделий в заказе
   */
  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('title')
      ->columns([
        ImageColumn::make('drawing')
          ->label(__('Photo'))
          ->state(function (OrderSection $record) {
            return $record->getPreviewUrl() ?? null;
          })
          ->circular()
          ->imageWidth(60)
          ->imageHeight(60),

        TextColumn::make('title')
          ->label(__('Name'))
          ->weight('bold')
          ->searchable(),

        TextColumn::make('specs')
          ->label(__('Technical Specifications'))
          ->state(function (OrderSection $record) {
            if (empty($record->specs)) {
              return '—';
            }

            return collect($record->specs)
              ->map(fn ($spec) => "▪ {$spec['label']}: {$spec['value']}")
              ->join("<br />");
          })
          ->wrap()
          ->html()
          ->color('gray')
          ->fontFamily('sans'),

        TextColumn::make('total_price')
          ->label(__('Total'))
          ->money($this->getOwnerRecord()->currency)
          ->weight('bold')
          ->color('primary')
          ->alignEnd(),
      ])
      ->recordActions([
        Action::make('view_estimate')
          ->label(__('Details'))
          ->icon('heroicon-o-document-text')
          ->color('info')
          ->modalHeading(fn (OrderSection $record) => $record->title)
          ->modalWidth(Width::FiveExtraLarge)
          ->modalSubmitAction(false)
          ->modalCancelActionLabel(__('Close'))

          
          ->fillForm(function (OrderSection $record): array {
            $state = [];

            
            foreach ($record->specs ?? [] as $spec) {
              $state["spec_{$spec['key']}"] = $spec['value'];
            }

            return $state;
          })

          ->schema([
            Grid::make(3)->schema([

              
              Grid::make(1)->schema([
                Section::make(__('Photo'))
                  ->schema([
                    TextEntry::make('drawing_preview')
                      ->hiddenLabel()
                      ->state(function (OrderSection $record) {
                        $url = $record->getPreviewUrl();
                        if (!$url) return '—';
                        return new HtmlString("<img src='{$url}' style='max-height: 200px; width: auto; object-fit: contain; border-radius: 8px; border: 1px solid rgba(0,0,0,0.1); margin: 0 auto;' />");
                      }),
                  ]),

                
                Section::make(__('Technical Specifications'))
                  ->collapsible()
                  ->collapsed(false) 
                  ->schema(function (OrderSection $record) {
                    $fields = [];
                    foreach ($record->specs ?? [] as $spec) {
                      $fields[] = TextInput::make("spec_{$spec['key']}")
                        ->label($spec['label'])
                        ->default($spec['value'])
                        ->disabled();
                    }
                    return $fields;
                  })
                  ->columns(2),
              ])->columnSpan(1),

              
              Section::make(__('Pricing & Economy'))
                ->columnSpan(2)
                ->schema([
                  TextEntry::make('estimate_table')
                    ->hiddenLabel()
                    ->state(function (OrderSection $record) {
                      $rows = '';
                      foreach ($record->items as $item) {
                        $price = number_format((float)$item->price, 2, '.', ' ') . ' ' . $record->order->currency;
                        $total = number_format((float)$item->total, 2, '.', ' ') . ' ' . $record->order->currency;
                        $rows .= "
                                                    <tr style='border-bottom: 1px solid #f3f4f6;'>
                                                        <td style='padding: 10px 12px; font-weight: 500; font-size: 0.85rem; color: #1f2937;'>{$item->name}</td>
                                                        <td style='padding: 10px 12px; text-align: center; font-size: 0.85rem; color: #4b5563;'>{$item->quantity} {$item->unit}</td>
                                                        <td style='padding: 10px 12px; text-align: right; font-size: 0.85rem; color: #4b5563; font-family: monospace;'>{$price}</td>
                                                        <td style='padding: 10px 12px; text-align: right; font-size: 0.85rem; font-weight: 600; color: #111827; font-family: monospace;'>{$total}</td>
                                                    </tr>
                                                ";
                      }

                      // Если позиций нет
                      if (empty($rows)) {
                        $rows = "<tr><td colspan='4' style='padding: 20px; text-align: center; color: #9ca3af;'>Сметные строки отсутствуют</td></tr>";
                      }

                      return new HtmlString("
                                                <div style='border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: #fff;'>
                                                    <table style='width: 100%; border-collapse: collapse;'>
                                                        <thead>
                                                            <tr style='background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #374151; font-size: 0.8rem; text-transform: uppercase;'>
                                                                <th style='padding: 12px; text-align: left;'>Услуга / Работа</th>
                                                                <th style='padding: 12px; text-align: center; width: 100px;'>Кол-во</th>
                                                                <th style='padding: 12px; text-align: right; width: 140px;'>Цена</th>
                                                                <th style='padding: 12px; text-align: right; width: 140px;'>Итого</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {$rows}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            ");
                    }),

                  
                  TextEntry::make('total_price_formatted')
                    ->hiddenLabel()
                    ->state(function (OrderSection $record) {
                      $formatted = number_format((float)$record->total_price, 2, '.', ' ') . ' ' . $record->order->currency;
                      return new HtmlString("
                                                <div style='display: flex; justify-content: flex-end; align-items: center; gap: 12px; padding: 16px 12px 4px 12px;'>
                                                    <span style='font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: #4b5563;'>Итого по изделию:</span>
                                                    <span style='font-size: 1.3rem; font-weight: 800; color: #10b981; font-family: monospace;'>{$formatted}</span>
                                                </div>
                                            ");
                    })
                    ->columnSpanFull(),
                ]),
            ]),
          ]),

        DeleteAction::make(),
      ])
      ->defaultSort('id', 'asc');
  }
}
