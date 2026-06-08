<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Forms\Tabs;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab as SchemaTab;
use Nicole\Box\Core\Models\Channel;
use Nicole\Box\Core\Models\SettingSchema;

class SalesChannelsTab
{
  public static function make(string $entityType): SchemaTab
  {
    return SchemaTab::make(__('Sales Channels'))
      ->icon('heroicon-o-globe-alt')
      ->visible(fn () => Channel::where('is_active', true)->exists())
      ->schema([
        Tabs::make('ChannelSettings')
          ->tabs(static::buildChannelTabs($entityType))
          ->columnSpanFull(),
      ]);
  }

  protected static function buildChannelTabs(string $entityType): array
  {
    $channels = Channel::where('is_active', true)->get();
    $schemaRecord = SettingSchema::where('entity_type', $entityType)->first();

    
    $fieldsConfig = $schemaRecord?->meta_schema ?? [];

    $tabs = [];
    foreach ($channels as $channel) {
      $tabs[] = SchemaTab::make($channel->name)
        ->icon('heroicon-o-queue-list')
        ->schema(static::buildDynamicFields($channel->code, $fieldsConfig))
        ->columns(2);
    }

    return $tabs;
  }

  protected static function buildDynamicFields(string $chCode, array $fields): array
  {
    $components = [];
    $locale = app()->getLocale();

    foreach ($fields as $f) {
      $key = "settings.channels.{$chCode}.{$f['key']}";

      $parsedOptions = [];
      if ($f['type'] === 'select' && isset($f['options'])) {
        foreach ($f['options'] as $optValue => $optLabel) {
          $parsedOptions[$optValue] = is_array($optLabel) ? ($optLabel[$locale] ?? $optValue) : $optLabel;
        }
      }

      $component = match ($f['type']) {
        'boolean' => Toggle::make($key),
        'number' => TextInput::make($key)->numeric(),
        'select' => Select::make($key)->options($parsedOptions)->native(false),
        default => TextInput::make($key),
      };

      $label = is_array($f['label']) ? ($f['label'][$locale] ?? $f['key']) : $f['label'];

      $components[] = $component
        ->label($label)
        ->columnSpan($f['width'] ?? 1);
    }

    return $components;
  }
}
