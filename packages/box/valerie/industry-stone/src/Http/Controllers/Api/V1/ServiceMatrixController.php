<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Nicole\Box\Core\Models\Attribute;
use Nicole\Box\Core\Models\Product;
use Valerie\Box\IndustryStone\Http\Resources\Api\V1\ServiceMatrixResource;

/**
 * @group Industry Stone: Калькулятор
 */
class ServiceMatrixController extends Controller
{
  /**
   * Матрица цен на услуги обработки.
   *
   * Возвращает плоский список активных услуг (вырезы, монтаж, доставка)
   * с привязанными тегами и расчетной матрицей цен в зависимости от целевого материала (Акрил/Кварц).
   */
  public function index(): JsonResponse
  {
    $channel = config('app.channel', Attribute::CHANNEL_WIDGET);

    $services = Product::query()
      ->where('catalog_type', 'service')
      ->where('is_active', true)
      ->publicInChannel($channel)
      ->with([
        'variants.attributeValues.option',
        'variants.prices',
        'unit',
        'attributeValues.attribute.complexDictionary',
        'attributeValues.option',
      ])
      ->get();

    return response()->json([
      /**
       * Статус выполнения запроса.
       * @var string
       * @example "success"
       */
      'status' => 'success',

      'data' => [
        /**
         * Список услуг с ценами для каждого материала.
         * @var \Illuminate\Http\Resources\Json\AnonymousResourceCollection<\Valerie\Box\IndustryStone\Http\Resources\Api\V1\ServiceMatrixResource>
         */
        'services' => ServiceMatrixResource::collection($services),
      ],
    ]);
  }
}
