<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache; // Импортируем фасад кэша [2]
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
  public function index(Request $request, string $family): \Illuminate\Http\JsonResponse
  {
    // Считываем лимит (поддерживаем и limit, и per_page) [1]
    $limit = (int)$request->input('limit', $request->input('per_page', 12));
    $familyCode = Str::singular($family);

    $id = $request->input('id');
    $productTypeCode = $request->input('product_type');
    $catalogType = $request->input('catalog_type');

    $channel = config('app.channel', Attribute::CHANNEL_WIDGET);
    $locale = app()->getLocale();
    $page = $request->input('page', 1);

    // Массив только динамических EAV характеристик
    $attributes = $request->input('attr', []);

    // Собираем массив только из строго поддерживаемых фильтров каталога (игнорируем случайные параметры) [2]
    $filterState = [
      'id' => $id,
      'product_type' => $productTypeCode,
      'catalog_type' => $catalogType,
      'attr' => $attributes,
    ];

    // Считываем глобальную версию каталога (по умолчанию 1) [2]
    $version = Cache::get('catalog_version', 1);

    // Строим стабильный ключ кэша [2]
    $cacheKey = "catalog_products_v{$version}_{$familyCode}_{$channel}_{$locale}_p{$page}_l{$limit}_" . md5(json_encode($filterState));

    // Кэшируем готовый скомпилированный массив ответа на 24 часа [1, 2]
    $responseData = Cache::remember($cacheKey, 86400, function () use ($limit, $familyCode, $id, $catalogType, $productTypeCode, $attributes, $channel) {
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
          'media',
          'attributeValues.attribute.complexDictionary',
          'attributeValues.attribute.productTypes', // Убирает EXISTS на атрибутах базового товара [2]
          'attributeValues.option',
          'attributeValues.complexRecord.dictionary', // Убирает N+1 на справочниках базового товара [2]
          'variants' => fn($v) => $v->where('is_active', true),
          'variants.product.type', // Убирает N+1 на типах родительских товаров для вариаций [2]
          'variants.media',
          'variants.attributeValues.attribute.productTypes', // Убирает EXISTS на атрибутах вариаций [2]
          'variants.attributeValues.option',
          'variants.attributeValues.complexRecord.dictionary', // Убирает N+1 на справочниках вариаций [2]
          'variants.prices.type.currency', // Убирает N+1 на валютах и типах цен [2]
        ])
        ->orderBy('sort_order')
        ->orderBy('created_at', 'desc');

      // Превращаем коллекцию ресурсов в чистый, готовый к кэшированию массив с сохранением структуры [1]
      return ProductResource::collection($query->paginate($limit))->response()->getData(true);
    });

    return response()->json($responseData);
  }
}
