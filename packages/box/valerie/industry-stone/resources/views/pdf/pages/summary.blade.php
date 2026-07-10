@php
  /** @var Order $order */
  use Nicole\Box\Core\Models\Order;
  use Valerie\Box\IndustryStone\Support\PdfEstimateRenderer;

  $sectionsCount = $order->sections->count();
  $sectionsWord = match (true) {
      $sectionsCount === 1 => 'Одно изделие',
      $sectionsCount > 1 && $sectionsCount < 5 => $sectionsCount . ' изделия',
      default => $sectionsCount . ' изделий'
  };

  // Распределяем изделия по страницам для исключения переполнения формата A4 в PDF
  $pages = [];
  $allSections = $order->sections->all();

  if ($sectionsCount > 0) {
      // На первой странице выводим главный заголовок и ровно 1 изделие
      $pages[] = [
          'is_first' => true,
          'sections' => [array_shift($allSections)]
      ];

      // На последующих страницах выводим до 2 изделий на страницу
      if (!empty($allSections)) {
          $chunks = array_chunk($allSections, 2);
          foreach ($chunks as $chunk) {
              $pages[] = [
                  'is_first' => false,
                  'sections' => $chunk
              ];
          }
      }
  }
@endphp

@foreach ($pages as $pageIndex => $pageData)
  <div class="page">

    <div class="top-gradient-line"></div>

    @include('valerie-stone::pdf.partials.header', ['subtitle' => 'Состав заказа'])

    <div class="page-content">
      @if ($pageData['is_first'])
        <div class="section-summary-header">
          <div class="section-summary-header-line"></div>
          <div class="section-summary-header-title">Состав заказа</div>
        </div>

        <h2 class="ligron-serif-title">
          {{ $sectionsWord }},<br>один проект
        </h2>
      @endif

      @foreach ($pageData['sections'] as $index => $section)
        @php
          $actualIndex = $pageData['is_first'] ? $index : 1 + ($pageIndex - 1) * 2 + $index;

          $meta = is_array($section->meta) ? $section->meta : json_decode((string)$section->meta, true);
          $productKey = $meta['properties']['product'] ?? 'kitchen';

          // Маппинг типов изделий на папки в public/pdf/layouts/
          $folderMap = [
              'bathroom' => 'countertop',
              'kitchen' => 'worktop',
              'windowsill' => 'windowsill',
          ];
          $folder = $folderMap[$productKey] ?? 'worktop';

          // Маппинг формы изделия на файлы (заменяем 'l-shaped' -> 'l_shaped')
          $formKey = $meta['properties']['form'] ?? 'line';
          $fileName = str_replace('-', '_', $formKey) . '.png';

          $localPath = public_path("pdf/layouts/{$folder}/{$fileName}");
          $staticLayoutBase64 = null;

          if (file_exists($localPath)) {
              $staticLayoutBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($localPath));
          }

          $hasImage = filled($staticLayoutBase64);
        @endphp

        <div class="section-card">
          <div class="section-card-header">
            <div class="section-header-title">
              0{{ $actualIndex + 1 }} · {{ $section->title }}
            </div>
            <div class="section-header-price">
              {{ number_format($section->price_grand_total, 0, '.', ' ') }} {{ $currencySymbol }}
            </div>
          </div>

          <div class="section-card-body">
            @if ($hasImage)
              <div class="section-card-body-cell" style="display: table-cell; width: 50%; vertical-align: middle; background-color: #F5F2EB; border-right: 1px solid #DDD9D0; text-align: center; padding: 15px;">
                @if ($staticLayoutBase64)
                  <img src="{{ $staticLayoutBase64 }}" alt="{{ $section->title }}" class="section-fallback-img">
                @endif
              </div>
            @endif

            <div class="section-body-right" style="width: {{ $hasImage ? '50%' : '100%' }} !important;">
              @if (!empty($section->description))
                <table class="specs-table">
                  @foreach ($section->description as $spec)
                    @if (!empty($spec['name']) && !empty($spec['description']))
                      <tr>
                        <td class="spec-label" style="width: {{ $hasImage ? '45%' : '25%' }} !important;">{{ $spec['name'] }}</td>
                        <td class="spec-value" style="width: {{ $hasImage ? '55%' : '75%' }} !important;">{{ $spec['description'] }}</td>
                      </tr>
                    @endif
                  @endforeach
                </table>
              @else
                <div class="specs-missing">Характеристики не указаны</div>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>

    @include('valerie-stone::pdf.partials.footer')
  </div>
@endforeach
