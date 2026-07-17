<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Nicole\Box\Core\Models\PriceType;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductVariant;
use Nicole\Box\Core\Models\ProductVariantPrice;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServiceMatrixTransferService
{
  /**
   * Стриминговый экспорт матрицы в CSV формат
   */
  public function export(Collection $services, Collection $materials): StreamedResponse
  {
    $retailPriceTypeId = PriceType::where('slug', 'retail')->value('id') ?? 1;

    // Сборка заголовков
    $headers = ['id', 'code', 'name'];
    foreach ($materials->keys() as $slug) {
      $headers[] = "{$slug}_cost";
      $headers[] = "{$slug}_markup";
    }

    $response = new StreamedResponse(function () use ($services, $headers, $materials, $retailPriceTypeId) {
      $handle = fopen('php://output', 'w');

      // UTF-8 BOM для совместимости с Excel
      fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

      fputcsv($handle, $headers, ';');

      foreach ($services as $service) {
        $row = [
          $service->id,
          $service->code,
          $service->getTranslation('name', 'ru') ?? $service->name,
        ];

        foreach ($materials->keys() as $slug) {
          $variant = $service->variants->first(function ($v) use ($slug) {
            return $v->attributeValues->contains(fn($av) => $av->option?->slug === $slug);
          });

          if ($variant) {
            $row[] = $variant->cost_price;

            $priceRecord = $variant->prices->firstWhere('price_type_id', $retailPriceTypeId);
            $row[] = $priceRecord ? $priceRecord->markup_percent : 0.0;
          } else {
            $row[] = '';
            $row[] = '';
          }
        }

        fputcsv($handle, $row, ';');
      }

      fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="services_price_matrix_' . date('Ymd_His') . '.csv"');

    return $response;
  }

  /**
   * Построчный импорт и валидация данных из CSV файла
   */
  public function import(string $tempFilePath, Collection $materials): int
  {
    $filePath = Storage::disk('local')->path($tempFilePath);

    if (!file_exists($filePath)) {
      throw new \InvalidArgumentException('Файл импорта не найден на диске.');
    }

    $handle = fopen($filePath, 'r');

    $bom = fread($handle, 3);
    if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
      rewind($handle);
    }

    $headers = fgetcsv($handle, 0, ';');
    if (!$headers) {
      fclose($handle);
      throw new \RuntimeException('Не удалось прочитать заголовки CSV.');
    }

    $retailPriceTypeId = PriceType::where('slug', 'retail')->value('id') ?? 1;
    $updatedCount = 0;

    while (($row = fgetcsv($handle, 0, ';')) !== false) {
      $rowData = array_combine($headers, $row);
      if (!$rowData) continue;

      $serviceId = $rowData['id'] ?? null;
      $serviceCode = $rowData['code'] ?? null;

      $service = null;
      if ($serviceId) {
        $service = Product::find($serviceId);
      } elseif ($serviceCode) {
        $service = Product::where('code', $serviceCode)->first();
      }

      if (!$service) continue;

      $hasUpdates = false;

      foreach ($materials->keys() as $slug) {
        $costKey = "{$slug}_cost";
        $markupKey = "{$slug}_markup";

        $variant = $service->variants()
          ->whereHas('attributeValues', function ($q) use ($slug) {
            $q->whereHas('option', fn($opt) => $opt->where('slug', $slug));
          })->first();

        if (!$variant) continue;

        if (isset($rowData[$costKey]) && $rowData[$costKey] !== '') {
          $newCost = (float)$rowData[$costKey];
          if ((float)$variant->cost_price !== $newCost) {
            $variant->update([
              'cost_price' => $newCost,
              'is_manual_pricing' => true,
            ]);
            $hasUpdates = true;
          }
        }

        if (isset($rowData[$markupKey]) && $rowData[$markupKey] !== '') {
          $newMarkup = (float)$rowData[$markupKey];

          $priceRecord = ProductVariantPrice::where([
            'product_variant_id' => $variant->id,
            'price_type_id' => $retailPriceTypeId,
          ])->first();

          if (!$priceRecord || (float)$priceRecord->markup_percent !== $newMarkup) {
            ProductVariantPrice::updateOrCreate(
              ['product_variant_id' => $variant->id, 'price_type_id' => $retailPriceTypeId],
              ['markup_percent' => $newMarkup]
            );
            $hasUpdates = true;
          }
        }
      }

      if ($hasUpdates) {
        $service->refreshMinPrice();
        $updatedCount++;
      }
    }

    fclose($handle);
    Storage::disk('local')->delete($tempFilePath);

    return $updatedCount;
  }
}
