@php

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
