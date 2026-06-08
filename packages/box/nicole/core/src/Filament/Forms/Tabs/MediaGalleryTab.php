<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Filament\Forms\Tabs;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs\Tab;

class MediaGalleryTab
{
    /**
     * Генерация вкладки с медиа-галереей.
     *
     * @param  bool  $isVariant  Если true, выводит специфичные для SKU подписи.
     */
    public static function make(bool $isVariant = false): Tab
    {
        return Tab::make(__('Media Gallery'))
            ->icon('heroicon-o-photo')
            ->schema([
                Grid::make(2)->schema([
                    
                    SpatieMediaLibraryFileUpload::make('main')
                        ->collection('main')
                        ->label(
                            $isVariant
                              ? __('Specific Variant Image (High Res)')
                              : __('Main Image (High Res)'),
                        )
                        ->image()
                        ->imageEditor(),

                    SpatieMediaLibraryFileUpload::make('preview')
                        ->collection('preview')
                        ->label(
                            $isVariant
                              ? __('Specific Preview Image (Thumbnail)')
                              : __('Preview Image (Thumbnail)'),
                        )
                        ->image()
                        ->imageEditor(),
                ]),
            ]);
    }
}
