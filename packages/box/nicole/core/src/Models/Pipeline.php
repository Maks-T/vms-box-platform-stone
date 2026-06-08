<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nicole\Box\Core\Traits\HasExternalCode;
use Nicole\Box\Core\Traits\HasSettings;
use Spatie\Translatable\HasTranslations;

class Pipeline extends Model
{
    use HasExternalCode;
    use HasSettings;
    use HasTranslations;

    protected $fillable = [
        'external_code',
        'name',
        'industry',
        'description',
        'ui_state',
        'is_active',
        'sort_order',
    ];

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'ui_state' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function rules(): HasMany
    {
        return $this->hasMany(BindingRule::class)->orderBy('sort_order');
    }
}
