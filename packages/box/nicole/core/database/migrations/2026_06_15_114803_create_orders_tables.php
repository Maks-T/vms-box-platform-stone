<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    // 1. Динамические статусы заказов
    Schema::create('order_statuses', function (Blueprint $table) {
      $table->id();
      $table->string('slug')->unique();
      $table->jsonb('name'); 
      $table->string('color', 30)->default('gray');

      $table->boolean('is_default')->default(false);
      $table->boolean('is_active')->default(true);
      $table->integer('sort_order')->default(0);
      $table->timestamps();
    });

    // 2. Покупатели / Клиенты
    Schema::create('customers', function (Blueprint $table) {
      $table->id();
      $table->string('first_name')->nullable();
      $table->string('last_name')->nullable();
      $table->string('middle_name')->nullable();

      $table->string('phone')->nullable();
      $table->string('phone_normalized', 20)->nullable()->index();
      $table->string('email')->nullable()->index();
      $table->string('address')->nullable();

      
      $table->text('admin_notes')->nullable();

      $table->ipAddress('last_ip')->nullable();
      $table->timestamps();
    });

    // 3. Заказы / Расчеты
    Schema::create('orders', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique()->index();
      $table->string('external_code')->nullable()->index();

      // Связь с покупателем
      $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

      // Финансовые показатели
      $table->decimal('grand_total', 15, 2)->default(0.00);
      $table->string('currency', 3)->default('RUB');

      // Язык, на котором был совершен расчет (для локализации PDF)
      $table->string('locale', 5)->default('ru');

      // Статус заказа
      $table->foreignId('status_id')->nullable()->constrained('order_statuses')->nullOnDelete();

      // Комментарии к заказу (клиента и менеджера)
      $table->text('customer_comment')->nullable();
      $table->text('manager_comment')->nullable();

      // Стейт калькулятора для восстановления
      $table->jsonb('calculator_state')->nullable();

      // Ответственный менеджер
      $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamps();
    });

    // 4. Секции / Изделия в заказе
    Schema::create('order_sections', function (Blueprint $table) {
      $table->id();
      $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

      $table->string('item_id'); 
      $table->string('title'); 
      $table->decimal('total_price', 15, 2)->default(0.00);

      // Характеристики конкретного изделия
      $table->jsonb('specs')->nullable();
      $table->timestamps();
    });

    // 5. Позиции сметы (строки материалов и работ, привязанные к секции)
    Schema::create('order_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

      // Связываем строку сметы с конкретной родительской секцией заказа
      $table->foreignId('order_section_id')->nullable()->constrained('order_sections')->cascadeOnDelete();

      // Связь с товаром
      $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();

      // Данные строки сметы
      $table->string('name');
      $table->decimal('quantity', 15, 3)->default(1.000);
      $table->string('unit')->default('шт.');
      $table->decimal('price', 15, 2)->default(0.00);
      $table->decimal('total', 15, 2)->default(0.00);

      // Группировка
      $table->string('group_id');
      $table->string('group_title');

      $table->timestamps();
    });

  }

  public function down(): void
  {
    Schema::dropIfExists('order_items');
    Schema::dropIfExists('order_sections');
    Schema::dropIfExists('orders');
    Schema::dropIfExists('customers');
    Schema::dropIfExists('order_statuses');
  }

};
