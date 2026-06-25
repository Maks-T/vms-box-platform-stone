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
          'phone_normalized' => $phoneNormalized,
          'email' => $customerData['email'] ?? null,
          'address' => $customerData['address'] ?? null,
          'last_ip' => $request->ip(),
        ]);
      }

      $prefix = env('VMS_ORDER_PREFIX', 'O');
      $year = date('y'); // "26"
      $sequence = Order::count() + 1;

      do {
        $suffix = strtoupper(Str::random(4));
        $orderCode = "{$prefix}-{$year}{$sequence}-{$suffix}";
      } while (Order::where('code', $orderCode)->exists());

      $statusId = OrderStatus::where('is_default', true)->value('id')
        ?? OrderStatus::where('is_active', true)->value('id');

      $calcState = $request->input('calc_state');

      $order = Order::create([
        'code' => $orderCode,
        'customer_id' => $customer->id,
        'grand_total' => $request->input('grand_total'),
        'currency' => $request->input('currency', 'RUB'),
        'locale' => app()->getLocale(),
        'status_id' => $statusId,
        'customer_comment' => $request->input('customer_comment'),
        'manager_comment' => $request->input('manager_comment'),
        'calc_state' => is_array($calcState) ? $calcState : json_decode((string)$calcState, true),
        'manager_id' => $request->input('manager_id'),
      ]);

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
}
