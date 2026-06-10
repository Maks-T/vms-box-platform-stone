<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Nicole\Box\Core\Models\Category;
use Nicole\Box\Core\Models\Product;
use Nicole\Box\Core\Models\ProductType;
use Nicole\Box\Core\Models\Unit;

class ProductFactory extends Factory
{
  protected $model = Product::class;

  public function definition(): array
  {
    $name = $this->faker->unique()->word();

    return [
      'catalog_type' => 'product', // по умолчанию физический товар
      'product_type_id' => ProductType::factory(), // Автоматически создает тип товара
      'category_id' => Category::factory(), // Автоматически создает категорию
      'unit_id' => Unit::factory(), // Автоматически создает единицу измерения
      'name' => [
        'ru' => 'Камень ' . ucfirst($name),
        'en' => 'Stone ' . ucfirst($name),
      ],
      'slug' => Str::slug($name),
      'description' => [
        'ru' => $this->faker->paragraph(),
        'en' => $this->faker->paragraph(),
      ],
      'min_price' => 0.0,
      'is_active' => true,
      'sort_order' => 0,
    ];
  }

  /**
   * Кастомное состояние: создание услуги вместо физического товара.
   */
  public function service(): self
  {
    return $this->state(fn (array $attributes) => [
      'catalog_type' => 'service',
      'name' => [
        'ru' => 'Услуга обработки',
        'en' => 'Processing service',
      ],
    ]);
  }

}
