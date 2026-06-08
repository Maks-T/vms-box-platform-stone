<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nicole\Box\Core\Traits\HasExternalCode;
use Nicole\Box\Core\Traits\HasSettings;
use Spatie\Translatable\HasTranslations;

class ProductFamily extends Model
{
  use HasExternalCode;
  use HasSettings;
  use HasTranslations;

  protected $fillable = [
    'external_code',
    'code',
    'slug', 
    'name',
    'icon',
    'meta_schema', 
    'sort_order',
    'is_active',
  ];

  public array $translatable = ['name'];

  protected function casts(): array
  {
    return [
      'is_active' => 'boolean',
      'meta_schema' => 'array', 
    ];
  }

  public function types(): HasMany
  {
    return $this->hasMany(ProductType::class, 'family_id');
  }
}
