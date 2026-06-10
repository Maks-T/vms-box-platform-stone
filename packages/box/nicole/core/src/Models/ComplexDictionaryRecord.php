<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nicole\Box\Core\Traits\HasExternalCode;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComplexDictionaryRecord extends Model
{
  use HasExternalCode;
  use HasTranslations;
  use HasFactory;

  protected $fillable = [
    'dictionary_id',
    'external_code',
    'slug',
    'name',
    'meta',
    'sort_order',
    'is_active',
  ];

  public array $translatable = ['name'];

  protected function casts(): array
  {
    return [
      'meta' => 'array',
      'sort_order' => 'integer',
      'is_active' => 'boolean',
    ];
  }

  public function dictionary(): BelongsTo
  {
    return $this->belongsTo(ComplexDictionary::class, 'dictionary_id');
  }

  protected static function booted(): void
  {
    $refreshLinkedProducts = function (ComplexDictionaryRecord $record) {


      $isFinancial = collect($record->dictionary->meta_schema ?? [])
        ->contains('type', ComplexDictionary::FIELD_TYPE_PRICE);

      // Если это не ценовой справочник (например, группы раскроя), прерываем работу
      if (!$isFinancial) {
        return;
      }

      // Ищем все EAV-привязки, где используется эта запись
      $eavs = ProductAttributeValue::where('value_complex_id', $record->id)->get();

      $productIds = [];
      foreach ($eavs as $eav) {
        if ($eav->attributable_type === (new Product)->getMorphClass()) {
          $productIds[] = $eav->attributable_id;
        } elseif ($eav->attributable_type === (new ProductVariant)->getMorphClass()) {
          $variant = ProductVariant::find($eav->attributable_id);
          if ($variant) {
            $productIds[] = $variant->product_id;
          }
        }
      }

      // Находим уникальные товары и запускаем их пересчет
      if (!empty($productIds)) {
        $products = Product::whereIn('id', array_unique($productIds))->get();
        foreach ($products as $product) {
          $product->refreshMinPrice();
        }
      }
    };

    static::saved($refreshLinkedProducts);
    static::deleted($refreshLinkedProducts);
  }

  protected static function newFactory(): \Nicole\Box\Core\Database\Factories\ComplexDictionaryRecordFactory
  {
    return \Nicole\Box\Core\Database\Factories\ComplexDictionaryRecordFactory::new();
  }

}
