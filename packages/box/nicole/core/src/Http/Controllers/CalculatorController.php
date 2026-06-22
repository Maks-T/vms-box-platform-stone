<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Http\Controllers;

use Illuminate\Http\Request;
use Nicole\Box\Core\Support\WidgetAssetHelper;
use Inertia\Inertia;
use Inertia\Response;

class CalculatorController
{
  /**
   * Отображение страницы калькулятора.
   *
   * @param Request $request
   * @param string|null $type Тип калькулятора
   * @return Response
   */
  public function show(Request $request, ?string $type = null): Response
  {

    $widgetSlug = 'calculator-app';

    $assets = WidgetAssetHelper::getAssets($widgetSlug);

    $initialData = [
      'apiUrl' => config('app.url') . '/api/v1',
      'assetsUrl' => config('app.url') . '/' . $widgetSlug . '/',
      'state' => null,
    ];

    return Inertia::render('Calculator/Show', [
      'assets' => $assets,
      'initialData' => $initialData,
      'currentType' => $type,
    ]);
  }

}
