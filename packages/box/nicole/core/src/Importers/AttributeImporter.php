<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Importers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Nicole\Box\Core\Importers\Contracts\ImportModuleInterface;
use Nicole\Box\Core\Models\Attribute;
use Nicole\Box\Core\Models\AttributeOption;
use Nicole\Box\Core\Models\ComplexDictionary;

class AttributeImporter implements ImportModuleInterface
{
  public function getName(): string
  {
    return 'Attributes & Options';
  }

  public function run(array $settings, array $data, Command $command): void
  {
    $attributes = $data['attributes'] ?? [];
    if (empty($attributes)) {
      return;
    }

    $bar = $command->getOutput()->createProgressBar(count($attributes));

    
    $complexDictMap = ComplexDictionary::pluck('id', 'code')->toArray();

    foreach ($attributes as $attrData) {

      // Если атрибут ссылается на умный справочник, ищем справочник с таким же кодом
      $complexDictId = null;
      if ($attrData['type'] === Attribute::TYPE_COMPLEX) {
        $complexDictId = $complexDictMap[$attrData['code']] ?? null;
      }

      // Создаем или обновляем сам Атрибут
      $attribute = Attribute::updateOrCreate(
        ['external_code' => $attrData['external_code']],
        [
          'code' => $attrData['code'],
          'name' => $attrData['name'],
          'type' => $attrData['type'],
          'complex_dictionary_id' => $complexDictId,
          'is_active' => true,
          'is_multiple' => $attrData['is_multiple'] ?? false,
          'settings' => $attrData['settings'] ?? null,
        ]
      );

      // Обрабатываем опции (цвета, бренды и т.д.), если они есть
      $sortOrder = 10;
      foreach ($attrData['options'] ?? [] as $optData) {
        $option = AttributeOption::updateOrCreate(
          ['external_code' => $optData['external_code']],
          [
            'attribute_id' => $attribute->id,
            'slug' => $optData['slug'],
            'value' => $optData['value'],
            
            'meta' => $optData['meta'] ?? null,
            'sort_order' => $sortOrder,
          ]
        );

        
        $imagePath = $optData['meta']['image'] ?? null;

        if ($imagePath) {
          
          $fullPath = base_path('import/export_images/' . ltrim($imagePath, '/'));

          if (File::exists($fullPath)) {
            
            $existingMedia = $option->getFirstMedia('main');
            $fileName = basename($fullPath);

            if (!$existingMedia || $existingMedia->file_name !== $fileName) {
              
              $option->clearMediaCollection('main');
              $option->addMedia($fullPath)
                ->preservingOriginal()
                
                ->withCustomProperties(['skip_conversions' => true])
                ->toMediaCollection('main');
            }
          } else {
            $command->warn("\n⚠ Опция {$option->slug}: Изображение не найдено -> {$fullPath}");
          }
        }

        $sortOrder += 10;
      }

      $bar->advance();
    }

    $bar->finish();
    $command->line('');
  }
}
