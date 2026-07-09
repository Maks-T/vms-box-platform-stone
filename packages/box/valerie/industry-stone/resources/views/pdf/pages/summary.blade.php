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
    <!-- Линия позиционируется абсолютно и не сдвигает текст -->
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
          // Вычисляем сквозной порядковый индекс изделия (01, 02, 03...)
          $actualIndex = $pageData['is_first'] ? $index : 1 + ($pageIndex - 1) * 2 + $index;
          $base64Images = PdfEstimateRenderer::resolveSectionImages($section);
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
            <div class="section-body-left">
              @forelse ($base64Images as $img)
                <div class="section-render-item">
                  <img src="{{ $img['base64'] }}" alt="{{ $section->title }}" style="max-height: {{ $img['height'] }}; width: auto; display: block; margin: 0 auto;">
                  @if (!empty($img['label']))
                    <span class="section-render-label">{{ $img['label'] }}</span>
                  @endif
                </div>
              @empty
                @if ($section->hasMedia('drawing'))
                  <img src="{{ $section->getFirstMediaPath('drawing') }}" alt="{{ $section->title }}" class="section-fallback-img">
                @else
                  <img src="https://placehold.co/400x300" alt="Изображение отсутствует" class="section-fallback-img">
                @endif
              @endforelse
            </div>

            <div class="section-body-right">
              @if (!empty($section->description))
                <table class="specs-table">
                  @foreach ($section->description as $spec)
                    @if (!empty($spec['name']) && !empty($spec['description']))
                      <tr>
                        <td class="spec-label">{{ $spec['name'] }}</td>
                        <td class="spec-value">{{ $spec['description'] }}</td>
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
