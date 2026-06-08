<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Nicole\Box\Core\Models\Attribute;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Http\Resources\Api\V1\ProductResource;

/**
 * @group Core: Каталог
 */
class ProductController extends Controller
{
  /**
   * Получить товары или услуги по коду семейства.
   *
   * Возвращает список активных товаров или услуг для указанного семейства.
   * Поддерживает пагинацию и динамическую фильтрацию.
   *
   * @param string $family Символьный код семейства (например: stone, sink, faucet, accessory).
   */
  public function index(Request $request, string $family): AnonymousResourceCollection
  {
    $limit = (int)$request->input('limit', 12);
    $familyCode = Str::singular($family);

    $id = $request->input('id');
    $productTypeCode = $request->input('product_type');
    $catalogType = $request->input('catalog_type');

    $channel = config('app.channel', Attribute::CHANNEL_WIDGET);

    // массив только динамических EAV характеристик
    $attributes = $request->input('attr', []);

    $query = Product::query()
      ->where('is_active', true)
      ->publicInChannel($channel)
      ->whereHas('type.family', fn($q) => $q->where('code', $familyCode))
      ->when($id, fn($q) => $q->where('id', $id))
      ->when($catalogType, fn($q) => $q->where('catalog_type', $catalogType))
      ->when($productTypeCode, fn($q) => $q->whereHas('type', fn($t) => $t->where('code', $productTypeCode)))
      ->filterByEav($attributes)
      ->with([
        'unit',
        'type',
        'attributeValues.attribute.complexDictionary',
        'attributeValues.option',
        'attributeValues.complexRecord',
        'variants' => fn($v) => $v->where('is_active', true),
        'variants.attributeValues.attribute',
        'variants.attributeValues.option',
        'variants.prices.type',
      ])
      ->orderBy('sort_order')
      ->orderBy('created_at', 'desc');

    return ProductResource::collection($query->paginate($limit));
  }

}
