@php
  /** @var \Nicole\Box\Core\Models\Order $order */
    use Valerie\Box\IndustryStone\Support\PdfEstimateRenderer;
@endphp

@foreach ($order->sections as $index => $section)
  <div class="page">
    <div class="top-gradient-line"></div>
    @include('valerie-stone::pdf.partials.header', ['subtitle' => 'Детальный расчёт'])

    <div class="page-content">
      <div class="section-summary-header">
        <div class="section-summary-header-line"></div>
        <div class="section-summary-header-title">Смета проекта</div>
      </div>

      <h2 class="ligron-serif-title-estimate">
        Детальный расчёт<br>0{{ $index + 1 }} — {{ $section->title }}
      </h2>

      <table class="estimate-table" style="display: table !important;">
        <thead>
        <tr class="estimate-table-th">
          <th class="estimate-th-left">Услуга / Работа</th>
          <th class="estimate-th-center">Кол-во</th>
          <th class="estimate-th-right">Цена</th>
          <th class="estimate-th-right-padding">Итого</th>
        </tr>
        </thead>
        <tbody>
        <tr class="estimate-section-header">
          <td colspan="3" class="estimate-section-title-cell">
            0{{ $index + 1 }} — {{ $section->title }}
            @php
              $stoneName = collect($section->description)->firstWhere('name', 'Наименование камня')['description'] ?? null;
            @endphp
            @if($stoneName)
              · {{ $stoneName }}
            @endif
          </td>
          <td class="estimate-section-price">
            {{ number_format($section->price_grand_total, 0, '.', ' ') }} {{ $currencySymbol }}
          </td>
        </tr>

        {!! PdfEstimateRenderer::renderRows($section->estimate ?? [], 0, $currencySymbol) !!}
        </tbody>
      </table>

      @if ($loop->last)
        @php
          $sectionsCount = $order->sections->count();
          $sectionsWord = match (true) {
              $sectionsCount === 1 => 'Одно изделие',
              $sectionsCount > 1 && $sectionsCount < 5 => "{$sectionsCount} изделия",
              default => "{$sectionsCount} изделий"
          };
        @endphp

        <div class="grand-total-card">
          <div class="grand-total-left">
            <div class="grand-total-label">Общая сумма · {{ mb_strtolower($sectionsWord) }}</div>
            <div class="grand-total-desc">Все работы, материалы, монтаж и доставка включены в стоимость</div>
          </div>
          <div class="grand-total-right">
            <div class="grand-total-price">
              {{ number_format($order->grand_total, 0, '.', ' ') }} {{ $currencySymbol }}
            </div>
          </div>
        </div>
      @endif
    </div>

    @include('valerie-stone::pdf.partials.footer')
  </div>
@endforeach
