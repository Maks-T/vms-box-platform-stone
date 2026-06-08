<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Resources\Products\Pages;

use Filament\Resources\Pages\CreateRecord;
use Nicole\Box\Core\Filament\Concerns\HasDynamicEavFields;
use Nicole\Box\Core\Filament\Resources\Products\ProductResource;

class CreateProduct extends CreateRecord
{
    use HasDynamicEavFields;

    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $this->saveEavData($this->record, $this->data['eav'] ?? []);
    }
}
