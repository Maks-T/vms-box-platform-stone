@php
  /** @var \Nicole\Box\Core\Models\Order $order */
    $currencySymbol = match($order->currency) {
        'RUB' => 'руб.',
        'USD' => '$',
        'BYN' => 'Br',
        default => $order->currency
    };
@endphp
  <!DOCTYPE html>
<html lang="{{ $order->locale ?? 'ru' }}">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>{{ $title ?? 'Коммерческое предложение' }}</title>

  <style>
    /* JOST: Regular */
    @font-face {
      font-family: 'Jost';
      src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(base_path('packages/box/valerie/industry-stone/resources/fonts/Jost/Jost-Regular.ttf'))) }}") format('truetype');
      font-weight: normal;
      font-style: normal;
    }

    /* JOST: Medium */
    @font-face {
      font-family: 'Jost';
      src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(base_path('packages/box/valerie/industry-stone/resources/fonts/Jost/Jost-Medium.ttf'))) }}") format('truetype');
      font-weight: 500;
      font-style: normal;
    }

    /* CORMORANT GARAMOND: Regular */
    @font-face {
      font-family: 'Cormorant Garamond';
      src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(base_path('packages/box/valerie/industry-stone/resources/fonts/CormorantGaramond/CormorantGaramond-Regular.ttf'))) }}") format('truetype');
      font-weight: normal;
      font-style: normal;
    }

    /* CORMORANT GARAMOND: Medium */
    @font-face {
      font-family: 'Cormorant Garamond';
      src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(base_path('packages/box/valerie/industry-stone/resources/fonts/CormorantGaramond/CormorantGaramond-Medium.ttf'))) }}") format('truetype');
      font-weight: 500;
      font-style: normal;
    }

    /* CORMORANT GARAMOND: Light */
    @font-face {
      font-family: 'Cormorant Garamond';
      src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(base_path('packages/box/valerie/industry-stone/resources/fonts/CormorantGaramond/CormorantGaramond-Light.ttf'))) }}") format('truetype');
      font-weight: 300;
      font-style: normal;
    }

    /* CORMORANT GARAMOND: Light Italic */
    @font-face {
      font-family: 'Cormorant Garamond';
      src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(base_path('packages/box/valerie/industry-stone/resources/fonts/CormorantGaramond/CormorantGaramond-LightItalic.ttf'))) }}") format('truetype');
      font-weight: 300;
      font-style: italic;
    }

    /* CENTURY */
    @font-face {
      font-family: 'Century';
      src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(base_path('packages/box/valerie/industry-stone/resources/fonts/Century/Century.ttf'))) }}") format('truetype');
      font-weight: normal;
      font-style: normal;
    }

    /* Подгружаем основные стили оформления */
    {!! file_get_contents(base_path('packages/box/valerie/industry-stone/resources/views/pdf/pdf-report.css')) !!}
  </style>
</head>
<body>

<!-- Страница 1: титульный лист (обложка) -->
@include('valerie-stone::pdf.pages.cover')

<!-- Страница 2: общий состав проекта -->
@include('valerie-stone::pdf.pages.summary')

<!-- Страница 3: сводная смета по всем изделиям -->
@include('valerie-stone::pdf.pages.estimate')

<!-- Страница 4: как мы работаем и контакты -->
@include('valerie-stone::pdf.pages.contacts')

</body>
</html>
