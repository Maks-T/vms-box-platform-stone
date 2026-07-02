<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Http\Controllers;

use Illuminate\Http\Request;
use Nicole\Box\Core\Support\WidgetAssetHelper;
use Nicole\Box\Core\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class CalculatorController
{
  /**
   * Отображение страницы калькулятора.
   *
   * @param Request $request
   * @param string|null $type Тип калькулятора (если задан)
   * @return Response
   */
  public function show(Request $request, ?string $type = null): Response
  {
    $widgetSlug = 'cpq-stone';

    $assets = WidgetAssetHelper::getAssets($widgetSlug);

    $order = null;

    if ($request->filled('code')) {
      $order = Order::where('code', $request->input('code'))->first();
    } elseif ($request->filled('orderId')) {
      $order = Order::find($request->input('orderId'));
    }

    $initialData = [
      'apiUrl' => config('app.url') . '/api/v1',
      'assetsUrl' => config('app.url') . '/storage/' . $widgetSlug . '/',
      'baseUrl' => config('app.url'),
      'policyLink' => config('nicole.policy_link', '#'),
      'ofertaLink' => config('nicole.oferta_link', '#'),
      'state' => $order ? $order->calc_state : null,
    ];

    return Inertia::render('Calculator/Show', [
      'assets' => $assets,
      'initialData' => $initialData,
      'currentType' => $type,
    ]);
  }

}
