@php
  /** @var \Nicole\Box\Core\Models\Order $order */
@endphp

<div class="page">
  <div class="top-gradient-line"></div>

  @include('valerie-stone::pdf.partials.header', ['subtitle' => 'Состав заказа'])

  <div class="page-content">
    @php
      $sectionsCount = $order->sections->count();
      $sectionsWord = match (true) {
          $sectionsCount === 1 => 'Одно изделие',
          $sectionsCount > 1 && $sectionsCount < 5 => "{$sectionsCount} изделия",
          default => "{$sectionsCount} изделий"
      };
    @endphp

    <div style="align-self: stretch; height: 13.59px; margin-bottom: 10px; position: relative">
      <div style="width: 16px; height: 1px; left: 0px; top: 6.30px; position: absolute; opacity: 0.60; background: #B8945A"></div>
      <div style="left: 22px; top: -1px; position: absolute; color: #B8945A; font-size: 8.50px; font-weight: 500; text-transform: uppercase; letter-spacing: 1.19px;">Состав заказа</div>
    </div>

    <h2 class="ligron-serif-title" style="font-size: 26px; line-height: 28px; margin: 0 0 25px 0;">
      {{ $sectionsWord }},<br>один проект
    </h2>

    @foreach ($order->sections as $index => $section)
      @php

        $folder = collect($section->specs)->firstWhere('key', 'product_type')['code'] ?? 'worktop';

        $shapeCode = collect($section->specs)->firstWhere('key', 'shape')['code'] ?? 'line';

        $fileName = str_replace('-', '_', $shapeCode) . '.png';

        $imagePath = public_path("pdf/layouts/{$folder}/{$fileName}");
        $base64Image = '';

        if (file_exists($imagePath)) {
            $base64Image = 'data:image/png;base64,' . base64_encode(file_get_contents($imagePath));
        }
      @endphp

      <div class="section-card">
        <div class="section-card-header">
          <div class="section-header-title">
            0{{ $index + 1 }} · {{ $section->title }}
          </div>
          <div class="section-header-price">
            {{ number_format($section->total_price, 0, '.', ' ') }} {{ $currencySymbol }}
          </div>
        </div>

        <div class="section-card-body">
          <div class="section-body-left">
            @if (!empty($base64Image))
              <!-- Выводим премиальный 3D-рендер формы изделия из соответствующей папки -->
              <img src="{{ $base64Image }}" alt="{{ $section->title }}">
            @elseif ($section->hasMedia('drawing'))
              <!-- Резервный вариант: выводим оригинальный чертеж калькулятора, если рендер не найден -->
              <img src="{{ $section->getFirstMediaPath('drawing') }}" alt="{{ $section->title }}">
            @else
              <!-- Дефолтная заглушка -->
              <img src="https://placehold.co/400x300" alt="Изображение отсутствует">
            @endif
          </div>

          <div class="section-body-right">
            <table class="specs-table">
              @foreach ($section->specs ?? [] as $spec)
                <!-- Пропускаем техническое поле shape_code [1] -->
                @if ($spec['key'] !== 'shape_code' && !empty($spec['label']) && !empty($spec['value']))
                  <tr>
                    <td class="spec-label">{{ $spec['label'] }}</td>
                    <td class="spec-value">{{ $spec['value'] }}</td>
                  </tr>
                @endif
              @endforeach
            </table>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  @include('valerie-stone::pdf.partials.footer', ['pageNum' => 2])
</div>