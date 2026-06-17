<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Http\Controllers\Api\V1;

use Illuminate\Routing\Controller;
use Nicole\Box\Core\Models\Order;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class PdfExportController extends Controller
{
  /**
   * Генерация и отдача PDF-потока в браузер (GET /orders/{code}/pdf)
   */
  public function streamPdf(string $code)
  {
    $payload = $this->getTemplateData($code);
    $template = config('nicole.pdf_template', 'nicole-core::pdf.calculator-report');

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($template, $payload)
      ->setPaper('a4', 'portrait')
      ->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'defaultFont' => 'dejavu sans',
      ]);

    return $pdf->stream("КП_Заказ_{$payload['order']->code}.pdf");
  }

  /**
   * Отдача чистой HTML-версии сметы
   */
  public function viewHtml(string $code)
  {
    $payload = $this->getTemplateData($code);
    $template = config('nicole.pdf_template', 'nicole-core::pdf.calculator-report');

    return view($template, $payload);
  }

  /**
   * Вспомогательный метод: собирает всю общую часть (заказ + QR-код)
   */
  protected function getTemplateData(string $code): array
  {
    // 1. Извлекаем заказ со всеми связями
    $order = Order::with(['customer', 'status', 'sections.items', 'manager'])
      ->where('code', $code)
      ->firstOrFail();

    // 2. Генерируем QR-код
    $targetUrl = config('app.url') . "/calculator?orderId=" . $order->id;
    $qrOptions = new QROptions([
      'outputType' => \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG,
      'eccLevel' => \chillerlan\QRCode\Common\EccLevel::H,
      'scale' => 5,
    ]);
    $qrCodeBase64 = (new QRCode($qrOptions))->render($targetUrl);

    // 3. Возвращаем единый массив данных
    return [
      'order' => $order,
      'title' => "КП № " . $order->code,
      'qrCode' => $qrCodeBase64,
    ];
  }
}
