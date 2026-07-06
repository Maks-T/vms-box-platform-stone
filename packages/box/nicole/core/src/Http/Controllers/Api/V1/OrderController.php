<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Http\Controllers\Api\V1;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Nicole\Box\Core\Http\Requests\Api\V1\SaveOrderRequest;
use Nicole\Box\Core\Models\Customer;
use Nicole\Box\Core\Models\Order;
use Nicole\Box\Core\Models\OrderSection;
use Nicole\Box\Core\Models\OrderProduct;
use Nicole\Box\Core\Models\OrderStatus;

/**
 * @group Core: Заказы
 */
class OrderController extends Controller
{
  /**
   * Сохранить новый расчет / заказ.
   *
   * Принимает полную спецификацию расчета из калькулятора.

   * Возвращает уникальный системный код заказа, дату его создания и готовые публичные ссылки для просмотра (HTML) и печати (PDF) сметного отчета.
   *
   * @param SaveOrderRequest $request Контролирует структуру входящих данных (SaveData)
   * @return JsonResponse Возвращает ID заказа, его код, дату создания и ссылки на PDF/HTML
   */
  public function save(SaveOrderRequest $request): JsonResponse
  {
    return DB::transaction(function () use ($request) {

      // Поиск или создание покупателя (только если передан номер телефона)
      $customer = null;
      $customerData = $request->input('customer');

      if (!empty($customerData['phone'])) {
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
            'phone_normalized' => $phoneNormalized,
            'email' => $customerData['email'] ?? null,
            'address' => $customerData['address'] ?? null,
            'last_ip' => $request->ip(),
          ]);
        }
      }

      // Генерация уникального кода КП
      $prefix = env('VMS_ORDER_PREFIX', 'O');
      $year = date('y'); // "26"
      $sequence = Order::count() + 1;

      do {
        $suffix = strtoupper(Str::random(4));
        $orderCode = "{$prefix}-{$year}{$sequence}-{$suffix}";
      } while (Order::where('code', $orderCode)->exists());

      // Получаем статус по умолчанию
      $statusId = OrderStatus::where('is_default', true)->value('id')
        ?? OrderStatus::where('is_active', true)->value('id');

      // Создаем основной Заказ
      $calcState = $request->input('calc_state');

      $order = Order::create([
        'code' => $orderCode,
        'customer_id' => $customer?->id,
        'grand_total' => $request->input('grand_total'),
        'currency' => $request->input('currency', 'RUB'),
        'locale' => app()->getLocale(),
        'status_id' => $statusId,
        'customer_comment' => $request->input('customer_comment'),
        'manager_comment' => $request->input('manager_comment'),
        'calc_state' => is_array($calcState) ? $calcState : json_decode((string)$calcState, true),
        'manager_id' => $request->input('manager_id'),
      ]);

      // Перебираем изделия (results) в запросе калькулятора
      foreach ($request->input('results', []) as $index => $resultData) {
        $price = $resultData['price'];
        $meta = $resultData['meta'] ?? [];

        $sectionTitle = $resultData['title'] ?? ('Изделие №' . ($index + 1));

        $section = OrderSection::create([
          'order_id' => $order->id,
          'item_id' => $resultData['id'] ?? ('result_' . $index),
          'type' => $resultData['type'] ?? ($meta['properties']['product'] ?? null),
          'title' => $sectionTitle,
          'price_total' => $price['total'],
          'price_grand_total' => $price['grand_total'],
          'price_vat' => $price['VAT'] ?? 0,
          'price_vat_percent' => $price['VAT_percent'] ?? 0,
          'price_discount' => $price['discount'] ?? 0,
          'price_discount_percent' => $price['discount_percent'] ?? 0,
          'description' => $resultData['description'] ?? null,
          'estimate' => $resultData['estimate'] ?? null,
          'meta' => $meta,
        ]);

        // Загружаем и прикрепляем чертежи к секции
        if (!empty($resultData['draw']) && is_array($resultData['draw'])) {
          foreach ($resultData['draw'] as $drawIndex => $base64Image) {
            if (str_starts_with($base64Image, 'data:image')) {
              try {
                $section->addMediaFromBase64($base64Image)
                  ->usingFileName("drawing_section_{$section->id}_{$drawIndex}.png")
                  ->usingName($section->title . " - Чертеж " . ($drawIndex + 1))
                  ->toMediaCollection('drawing');
              } catch (\Throwable $e) {
                Log::error("Failed to save drawing {$drawIndex} for order section {$section->id}: " . $e->getMessage());
              }
            }
          }
        }

        // Сохраняем физические товары
        $items = $meta['items'] ?? [];
        if (is_array($items)) {
          foreach ($items as $groupKey => $subItems) {
            if (is_array($subItems)) {
              foreach ($subItems as $subItem) {
                if (!empty($subItem['variant_id'])) {
                  $variantId = (int) $subItem['variant_id'];

                  OrderProduct::create([
                    'order_id' => $order->id,
                    'order_section_id' => $section->id,
                    'product_variant_id' => $variantId,
                    'quantity' => $subItem['quantity'] ?? 1.000,
                  ]);
                }
              }
            }
          }
        }
      }

      $pdfUrl = url("/api/v1/orders/{$orderCode}/pdf");
      $htmlUrl = url("/api/v1/orders/{$orderCode}/html");

      return response()->json([
        'status' => 'success',
        'message' => 'Заказ, спецификации и сметные товары успешно сохранены.',
        'data' => [
          'order_id' => $order->id,
          'code' => $orderCode,
          'external_code' => $order->external_code,
          'pdf_url' => $pdfUrl,
          'html_url' => $htmlUrl,
          'created_at' => $order->created_at->toIso8601String(),
          'created_at_formatted' => $order->created_at->format('d.m.Y H:i'),
        ]
      ], 201);
    });
  }

  /**
   * Получить данные заказа по коду
   *
   * @param string $code
   * @return JsonResponse
   */
  public function get(string $code): JsonResponse
  {

    $order = Order::with('customer')->where('code', $code)->firstOrFail();

    return response()->json([
      'status' => true,
      'data' => [
        'id' => $order->id,
        'code' => $order->code,
        'grand_total' => $order->grand_total,
        'currency' => $order->currency,
        'locale' => $order->locale,
        'calc_state' => $order->calc_state,
        'customer' => $order->customer ? [
          'id' => $order->customer->id,
          'first_name' => $order->customer->first_name,
          'last_name' => $order->customer->last_name,
          'middle_name' => $order->customer->middle_name,
          'phone' => $order->customer->phone,
          'phone_normalized' => $order->customer->phone_normalized,
          'email' => $order->customer->email,
          'address' => $order->customer->address,
        ] : null,
        'created_at' => $order->created_at->toIso8601String(),
        'updated_at' => $order->updated_at->toIso8601String(),
      ]
    ]);
  }

}
