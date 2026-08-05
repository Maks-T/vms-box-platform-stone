<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Nicole\Box\Core\Http\Resources\Api\V1\ProductResource;
use Nicole\Box\Core\Models\Product;

Route::get('/', function () {
  return Inertia::render('Catalog/Index');
})->name('catalog');

Route::get('/about', function () {
  return Inertia::render('About/Index');
})->name('about');

Route::get('/bootstrap', function () {
  return Inertia::render('Bootstrap/Index');
})->name('bootstrap');

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

Route::get('/services', function () {
  return Inertia::render('Services/Index');
})->name('services');

Route::get('/lang/{locale}', function (string $locale) {
  if (in_array($locale, ['ru'])) {
    session(['locale' => $locale]);
    cookie()->queue(cookie()->forever('filament_language_switch_locale', $locale));
  }

  return back();
})->name('lang.switch');