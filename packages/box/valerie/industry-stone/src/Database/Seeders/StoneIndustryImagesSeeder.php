<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Nicole\Box\Core\Models\AttributeOption;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductVariant;

class StoneIndustryImagesSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('import/import_ready_filtered.json');
        $imagesBasePath = base_path('import/export_images');

        if (! File::exists($jsonPath) || ! is_dir($imagesBasePath)) {
            $this->command->error('Image files or JSON not found!');

            return;
        }

        $data = json_decode(File::get($jsonPath), true);

        $this->command->info(
            'Starting fast image import (thumbnail generation disabled)...',
        );

        $this->importDictionaryImages($data['dictionaries'] ?? [], $imagesBasePath);

        $this->importProductImages($data['items'] ?? [], $imagesBasePath);

        $this->command->info('Image import completed successfully!');
    }

    private function importDictionaryImages(
        array $dictionaries,
        string $imagesBasePath,
    ): void {
        $this->command->info('Importing dictionary icons (colors/textures)...');

        foreach ($dictionaries as $attrCode => $options) {
            foreach ($options as $optionData) {
                if (empty($optionData['payload']['image'])) {
                    continue;
                }

                $imagePath = $imagesBasePath.$optionData['payload']['image'];

                if (File::exists($imagePath)) {
                    $optionModel = AttributeOption::whereHas(
                        'attribute',
                        fn ($q) => $q->where('code', $attrCode),
                    )
                        ->where('slug', $optionData['slug'])
                        ->first();

                    if ($optionModel && ! $optionModel->hasMedia('main')) {
                        $optionModel
                            ->addMedia($imagePath)
                            ->preservingOriginal()
                            ->withCustomProperties(['skip_conversions' => true])
                            ->toMediaCollection('main');
                    }
                }
            }
        }
    }

    private function importProductImages(
        array $items,
        string $imagesBasePath,
    ): void {
        $totalItems = count($items);
        $this->command->info(
            "Importing photos for {$totalItems} products and their SKUs...",
        );

        $count = 0;

        foreach ($items as $item) {
            $product = Product::where('external_code', (string) $item['id'])->first();
            if ($product) {
                $this->attachImage($product, $item, $imagesBasePath);
            }

            foreach ($item['variants'] ?? [] as $variantData) {
                $variant = ProductVariant::where('sku', $variantData['slug'])->first();
                if ($variant) {
                    $this->attachImage($variant, $variantData, $imagesBasePath);
                }
            }

            $count++;
            if ($count % 20 === 0) {
                $this->command->info(
                    " - Processed photos for {$count} / {$totalItems} products...",
                );
            }
        }

        $this->command->info("Finished: loaded images for {$count} products.");
    }

    private function attachImage(
        $model,
        array $data,
        string $imagesBasePath,
    ): void {
        $previewPath = $data['preview_picture'] ?? null;
        $detailPath = $data['detail_picture'] ?? null;

        if ($previewPath && File::exists($imagesBasePath.$previewPath)) {
            if (! $model->hasMedia('preview')) {
                $model
                    ->addMedia($imagesBasePath.$previewPath)
                    ->preservingOriginal()
                    ->withCustomProperties(['skip_conversions' => true])
                    ->toMediaCollection('preview');
            }
        }

        if ($detailPath && File::exists($imagesBasePath.$detailPath)) {
            if (! $model->hasMedia('main')) {
                $media = $model
                    ->addMedia($imagesBasePath.$detailPath)
                    ->preservingOriginal();

                if ($previewPath) {
                    $media->withCustomProperties(['skip_conversions' => true]);
                }
                $media->toMediaCollection('main');
            }
        }
    }
}
