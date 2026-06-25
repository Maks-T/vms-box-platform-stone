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
        // Получаем тип калькулятора (например, 'windowsill' или 'countertop')
        $folder = $section->type ?? 'worktop';
        $base64Images = [];

        if ($folder === 'windowsill') {
            // ЧИТАЕМ ИЗДЕНИЯ НАПРЯМУЮ: Обходим массив подоконников из мета-данных
            $windowsills = $section->meta['items']['windowsills'] ?? [];

            // Динамически вычисляем высоту картинок в зависимости от их количества
            $imgHeight = count($windowsills) === 1 ? '180px' : '100px';

            foreach ($windowsills as $sill) {
                // Читаем системный латинский код формы (line, corner, bay) напрямую!
                $shapeCode = $sill['meta']['form'] ?? 'line';
                $shapeFile = str_replace('-', '_', $shapeCode) . '.png';

                $imagePath = public_path("pdf/layouts/windowsill/{$shapeFile}");
                if (file_exists($imagePath)) {
                    $base64Images[] = [
                        'label' => $sill['title'] ?? 'Подоконник', // Передаем название подоконника
                        'base64' => 'data:image/png;base64,' . base64_encode(file_get_contents($imagePath)),
                        'height' => $imgHeight
                    ];
                }
            }
        } else {
            // Для столешниц берем код формы из мета-свойств
            $shapeCode = $section->meta['properties']['shape_code'] ?? 'line';
            $fileName = str_replace('-', '_', $shapeCode) . '.png';
            $imagePath = public_path("pdf/layouts/{$folder}/{$fileName}");

            if (file_exists($imagePath)) {
                $base64Image = 'data:image/png;base64,' . base64_encode(file_get_contents($imagePath));
                $base64Images[] = [
                    'base64' => $base64Image,
                    'label' => null,
                    'height' => '250px'
                ];
            }
        }
      @endphp

      <div class="section-card">
        <div class="section-card-header">
          <div class="section-header-title">
            0{{ $index + 1 }} · {{ $section->title }}
          </div>
          <div class="section-header-price">
            {{ number_format($section->price_grand_total, 0, '.', ' ') }} {{ $currencySymbol }}
          </div>
        </div>

        <div class="section-card-body">
          <div class="section-body-left" style="vertical-align: middle;">
            @if (!empty($base64Images))
              <!-- Выводим рендеры с динамической высотой -->
              @foreach ($base64Images as $img)
                <div style="margin-bottom: 12px; text-align: center; page-break-inside: avoid;">
                  <img src="{{ $img['base64'] }}" alt="{{ $section->title }}" style="max-height: {{ $img['height'] }}; width: auto; display: block; margin: 0 auto;">
                  @if ($img['label'])
                    <span style="font-size: 7.5px; color: #5A5750; display: block; margin-top: 1px; text-transform: uppercase; letter-spacing: 0.5px;">{{ $img['label'] }}</span>
                  @endif
                </div>
              @endforeach
            @elseif ($section->hasMedia('drawing'))
              <!-- Резервный вариант: чертеж из калькулятора -->
              <img src="{{ $section->getFirstMediaPath('drawing') }}" alt="{{ $section->title }}" style="max-height: 180px; width: auto; display: block; margin: 0 auto;">
            @else
              <!-- Дефолтная заглушка -->
              <img src="https://placehold.co/400x300" alt="Изображение отсутствует" style="max-height: 180px; width: auto; display: block; margin: 0 auto;">
            @endif
          </div>

          <div class="section-body-right">
            <table class="specs-table">
              @foreach ($section->description ?? [] as $spec)
                @if (!empty($spec['name']) && !empty($spec['description']))
                  <tr>
                    <td class="spec-label">{{ $spec['name'] }}</td>
                    <td class="spec-value">{{ $spec['description'] }}</td>
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
