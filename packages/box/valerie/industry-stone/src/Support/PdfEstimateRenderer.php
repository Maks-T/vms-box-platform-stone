<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Support;

use Nicole\Box\Core\Models\OrderSection;

class PdfEstimateRenderer
{
  /**
   * Резолвит массив изображений (схем/чертежей) для секции заказа в формате base64.
   *
   * @param OrderSection $section
   * @return array<int, array{base64: string, label: string|null, height: string}>
   */
  public static function resolveSectionImages(OrderSection $section): array
  {
    $productType = $section->type ?? 'kitchen';
    $folder = $productType === 'kitchen' ? 'worktop' : $productType;
    $base64Images = [];

    if ($folder === 'windowsill') {
      $windowsills = $section->meta['items']['windowsills'] ?? [];
      $imgHeight = count($windowsills) === 1 ? '180px' : '100px';

      foreach ($windowsills as $sill) {
        $shapeCode = $sill['meta']['form'] ?? 'line';
        $shapeFile = str_replace('-', '_', $shapeCode) . '.png';

        $imagePath = public_path("pdf/layouts/windowsill/{$shapeFile}");
        if (file_exists($imagePath)) {
          $base64Images[] = [
            'label' => $sill['title'] ?? 'Подоконник',
            'base64' => 'data:image/png;base64,' . base64_encode(file_get_contents($imagePath)),
            'height' => $imgHeight
          ];
        }
      }
    } else {
      // Для столешниц (кухня/ванная) берем код формы из мета-свойств
      $shapeCode = $section->meta['properties']['form'] ?? 'line';
      $fileName = str_replace('-', '_', $shapeCode) . '.png';
      $imagePath = public_path("pdf/layouts/{$folder}/{$fileName}");

      if (file_exists($imagePath)) {
        $base64Image = 'data:image/png;base64,' . base64_encode(file_get_contents($imagePath));
        $base64Images[] = [
          'base64' => $base64Image,
          'label' => null,
          'height' => '250px'
        ];
      }
    }

    return $base64Images;
  }

  /**
   * Рекурсивный рендеринг строк вложенной сметы для PDF.
   */
  public static function renderRows(array $treeItems, int $depth = 0, string $currencySymbol = 'руб.'): string
  {
    $html = '';

    foreach ($treeItems as $index => $item) {
      $cells = $item['value'] ?? [];

      if ($depth === 0 && $index === 0 && count($cells) > 0 && str_contains(strtolower($cells[0]), 'название')) {
        continue;
      }

      $rowStyle = $depth === 0 ? 'font-weight: bold; background-color: #F5F2EB; border-top: 1.5px solid #1A1916;' : '';

      $html .= "<tr class='estimate-row' style='{$rowStyle}'>";

      $cellCount = count($cells);
      $name = $cells[0] ?? '—';

      if ($depth > 0) {
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);
        $name = $indent . '· ' . $name;
      }

      if ($cellCount === 2) {
        $totalVal = $cells[1] ?? '';
        $html .= "<td class='estimate-cell-name' colspan='3'>{$name}</td>";
        $html .= "<td class='estimate-cell-total'>{$totalVal}</td>";
      } else {
        $qtyVal = $cells[1] ?? '';
        $unit = $cells[2] ?? '';
        $priceVal = $cells[3] ?? '';
        $totalVal = $cells[4] ?? '';

        $html .= "<td class='estimate-cell-name'>{$name}</td>";
        $html .= "<td class='estimate-cell-qty'>{$qtyVal} {$unit}</td>";
        $html .= "<td class='estimate-cell-price'>{$priceVal}</td>";
        $html .= "<td class='estimate-cell-total'>{$totalVal}</td>";
      }

      $html .= "</tr>";

      if (!empty($item['children']) && is_array($item['children'])) {
        $html .= self::renderRows($item['children'], $depth + 1, $currencySymbol);
      }
    }

    return $html;
  }
}
