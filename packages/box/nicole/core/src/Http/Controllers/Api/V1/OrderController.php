<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Http\Controllers\Api\V1;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nicole\Box\Core\Http\Requests\Api\V1\SaveOrderRequest;
use Nicole\Box\Core\Models\Customer;
use Nicole\Box\Core\Models\Order;
use Nicole\Box\Core\Models\OrderSection;
use Nicole\Box\Core\Models\OrderItem;
use Nicole\Box\Core\Models\OrderStatus;

class OrderController extends Controller
{
  /**
   * Сохранение заказа из калькулятора (POST /order/save)
   */
  public function save(SaveOrderRequest $request): JsonResponse
  {
    return DB::transaction(function () use ($request) {

      // 1. Поиск или создание покупателя
      $customerData = $request->input('customer');
      $phone = (string)$customerData['phone'];
      $phoneNormalized = preg_replace('/[^0-9]/', '', $phone);

      $customer = Customer::where('phone_normalized', $phoneNormalized)->first();

      if (!$customer) {
        $nameParts = explode(' ', trim($customerData['name'] ?? ''));
        $lastName = $nameParts[0] ?? null;
        $firstName = $nameParts[1] ?? ($customerData['name'] ?? 'Клиент');
        $middleName = $nameParts[2] ?? null;

        $customer = Customer::create([
          'first_name' => $firstName,
          'last_name' => $lastName,
          'middle_name' => $middleName,
          'phone' => $phone,
          'email' => $customerData['email'] ?? null,
          'address' => $customerData['address'] ?? null,
          'last_ip' => $request->ip(),
        ]);
      }

      // 2. Получаем системный статус по умолчанию
      $statusId = OrderStatus::where('is_default', true)->value('id')
        ?? OrderStatus::where('is_active', true)->value('id');

      // 3. Создаем основной Заказ
      $order = Order::create([
        'customer_id' => $customer->id,
        'grand_total' => $request->input('grand_total'),
        'currency' => $request->input('currency', 'RUB'),
        'locale' => app()->getLocale(),
        'status_id' => $statusId,
        'customer_comment' => $request->input('customer_comment'),
        'manager_comment' => $request->input('manager_comment'),
        'calculator_state' => $request->input('calculator_state'),
        'manager_id' => $request->input('manager_id'),
      ]);

      // 4. Перебираем секции (изделия) в JSON-запросе
      foreach ($request->input('items', []) as $itemData) {
        $itemId = $itemData['id'];
        $itemTitle = $itemData['title'];

        // Создаем секцию заказа
        $section = OrderSection::create([
          'order_id' => $order->id,
          'item_id' => $itemId,
          'title' => $itemTitle,
          'total_price' => $itemData['total_price'],
          'specs' => $itemData['specs'] ?? null,
        ]);

        // 5. Загружаем и прикрепляем чертеж base64 напрямую к OrderSection
        if (!empty($itemData['draw']) && str_starts_with($itemData['draw'], 'data:image')) {
          try {
            $section->addMediaFromBase64($itemData['draw'])
              ->usingFileName("drawing_section_{$section->id}.png")
              ->usingName($itemTitle)
              ->toMediaCollection('drawing');
          } catch (\Throwable $e) {
            Log::error("Failed to save drawing for order section {$section->id}: " . $e->getMessage());
          }
        }

        // 6. Перебираем сметные группы внутри секции и пишем позиции
        foreach ($itemData['estimate_groups'] as $groupData) {
          $groupId = $groupData['id'];
          $groupTitle = $groupData['title'];

          foreach ($groupData['items'] as $subItem) {
            OrderItem::create([
              'order_id' => $order->id,
              'order_section_id' => $section->id, // Связываем с созданной секцией
              'product_variant_id' => $subItem['product_variant_id'] ?? null,
              'name' => $subItem['name'],
              'quantity' => $subItem['quantity'],
              'unit' => $subItem['unit'] ?? 'шт.',
              'price' => $subItem['price'],
              'total' => $subItem['total'],
              'group_id' => $groupId,
              'group_title' => $groupTitle,
            ]);
          }
        }
      }

      return response()->json([
        'status' => 'success',
        'message' => 'Заказ и сметные секции успешно сохранены.',
        'data' => [
          'order_id' => $order->id,
          'external_code' => $order->external_code,
        ]
      ], 201);
    });
  }
}
