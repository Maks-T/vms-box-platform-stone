<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('product_attribute_values', function (Blueprint $table) {
      // Композитный индекс для полиморфного EAV-поиска
      $table->index(
        ['attributable_type', 'attributable_id', 'attribute_id'],
        'idx_eav_lookup_compound'
      );
    });

    Schema::table('product_variants', function (Blueprint $table) {
      // Индекс на вязь вариации с базовым товаром
      $table->index('product_id', 'idx_product_variants_product_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('product_variants', function (Blueprint $table) {
      $table->dropIndex('idx_product_variants_product_id');
    });

    Schema::table('product_attribute_values', function (Blueprint $table) {
      $table->dropIndex('idx_eav_lookup_compound');
    });
  }

};
