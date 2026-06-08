<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Nicole\Box\Core\Traits\HasExternalCode;
use Nicole\Box\Core\Traits\HasSettings;
use Spatie\Translatable\HasTranslations;

class Unit extends Model
{
  use HasExternalCode;
  use HasSettings;
  use HasTranslations;

  protected $fillable = ['slug', 'code', 'name', 'symbol', 'sort_order']; 

  public array $translatable = ['name', 'symbol'];
}
