<?php

namespace Nicole\Box\Core\Filament\Resources\PriceTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Nicole\Box\Core\Filament\Resources\PriceTypes\PriceTypeResource;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Support\Filament\ProtectDefaultRecord;

class EditPriceType extends EditRecord
{
    protected static string $resource = PriceTypeResource::class;

  protected function getHeaderActions(): array
  {
    return [
      ProtectDefaultRecord::pageDeleteAction('Cannot delete default record'),
    ];
  }
}
