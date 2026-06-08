<?php

namespace Nicole\Box\Core\Filament\Resources\Currencies\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Nicole\Box\Core\Filament\Resources\Currencies\CurrencyResource;
use Nicole\Box\Core\Support\Filament\ProtectDefaultRecord;

class EditCurrency extends EditRecord
{
  protected static string $resource = CurrencyResource::class;

  protected function getHeaderActions(): array
  {
    return [
      ProtectDefaultRecord::pageDeleteAction('Cannot delete default record'),
    ];
  }
}
