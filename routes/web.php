<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Nicole\Box\Core\Http\Resources\Api\V1\ProductResource;
use Nicole\Box\Core\Models\Product;

// 1. Главная страница (Каталог)
Route::get('/', function () {
  return Inertia::render('Catalog/Index');
})->name('catalog');

// Страница конфигурации Bootstrap
Route::get('/bootstrap', function () {
  return Inertia::render('Bootstrap/Index');
})->name('bootstrap');

// Детальная страница товара
Route::get('/product/{slug}', function (string $slug) {
  $product = Product::where('slug', $slug)
    ->where('is_active', true)
    ->with([
      'unit',
      'type.family',
      'attributeValues.attribute.complexDictionary',
      'attributeValues.option',
      'attributeValues.complexRecord',
      'variants' => fn($v) => $v->where('is_active', true),
      'variants.attributeValues.attribute',
      'variants.attributeValues.option',
      'variants.prices.type',
    ])
    ->firstOrFail();

  $productData = (new ProductResource($product))->toArray(request());

  return Inertia::render('Product/Show', [
    'product' => $productData,
    'familyCode' => $product->type->family->code ?? 'stone'
  ]);
})->name('product.show');

// Страница услуг калькулятора
Route::get('/services', function () {
  return Inertia::render('Services/Index');
})->name('services');

// Переключение языка
Route::get('/lang/{locale}', function (string $locale) {
  if (in_array($locale, ['ru', 'en'])) {
    // Записываем в сессию для нашего API
    session(['locale' => $locale]);

    // Записываем в куку, чтобы Filament Language Switch не сбрасывал язык
    cookie()->queue(cookie()->forever('filament_language_switch_locale', $locale));
  }

  return back();
})->name('lang.switch');
