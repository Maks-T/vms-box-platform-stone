<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Nicole\Box\Core\Models\ComplexDictionary;
use Nicole\Box\Core\Services\PricingManager;
use Nicole\Box\Core\Support\Constants\SettingKey as SK;

/**
 * @mixin ComplexDictionary
 */
class ComplexDictionaryResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    $channel = config('app.channel', 'widget');
    $chanSettings = $this->settings['channels'][$channel] ?? [];

    if (! ($chanSettings[SK::IS_PUBLIC] ?? true)) {
      return [];
    }

    
    $schema = $this->meta_schema ?? [];

    $pricingManager = app(PricingManager::class);
    $baseCurrency = $pricingManager->baseCurrency->code;
    $locale = app()->getLocale();

    $isSettingsPublic = $chanSettings[SK::IS_SETTINGS_PUBLIC] ?? false;
    $publicSchema = null;

    if ($isSettingsPublic && is_array($schema)) {
      $publicSchema = [];
      foreach ($schema as $field) {
        if (! ($field['is_public'] ?? true)) continue;

        $label = is_array($field['label']) ? ($field['label'][$locale] ?? $field['key']) : ($field['label'] ?? $field['key']);

        $key = $field['type'] === ComplexDictionary::FIELD_TYPE_PRICE
          ? $field['key'] . ComplexDictionary::TOTAL_SUFFIX
          : $field['key'];

        $publicSchema[] = [
          'key' => $key,
          'type' => $field['type'],
          'label' => $label,
        ];
      }
    }

    return [
      /**
       * Системный код справочника (напр., price_group).
       * @var string
       */
      'code' => $this->code,

      /**
       * Название справочника.
       * @var string
       */
      'name' => (string) $this->name,

      /**
       * Схема полей.
       * @var array<int, array{key: string, type: string, label: string}>|null
       */
      'schema' => $publicSchema,

      /**
       * Элементы справочника.
       * @var array<int, array{id: int, slug: string, name: string, meta: object}>
       */
      'records' => $this->records
        ->map(function ($record) use ($schema, $pricingManager, $baseCurrency) {
          $payload = $record->meta ?? [];
          $safeMeta = [];

          foreach ($schema as $field) {
            $key = $field['key'];
            $isFieldPublic = $field['is_public'] ?? true;

            if (! $isFieldPublic) continue;

            if ($field['type'] === ComplexDictionary::FIELD_TYPE_PRICE) {
              $cost = (float) ($payload[$key] ?? 0);
              $markup = (float) ($payload[$key.ComplexDictionary::MARKUP_SUFFIX] ?? 0);

              if ($cost > 0) {
                $sourceCurrency = $field['currency'] ?? 'USD';
                $convertedCost = $pricingManager->convert($cost, $sourceCurrency, $baseCurrency);
                $safeMeta[$key.ComplexDictionary::TOTAL_SUFFIX] = (float) round($convertedCost * (1 + $markup / 100), 2);
              } else {
                $safeMeta[$key.ComplexDictionary::TOTAL_SUFFIX] = 0.0;
              }
            } else {
              $safeMeta[$key] = $payload[$key] ?? null;
            }
          }

          return [
            'id' => $record->id,
            'slug' => $record->slug ?? ($record->external_code ?? (string) $record->id),
            'name' => (string) $record->name,
            'meta' => (object) $safeMeta,
          ];
        })
        ->toArray(),
    ];
  }
}
