<div class="page">
  <div class="top-gradient-line"></div>
  @include('valerie-stone::pdf.partials.header', ['subtitle' => 'Детальный расчёт'])

  <div class="page-content">
    <div style="align-self: stretch; height: 13.59px; margin-bottom: 10px; position: relative">
      <div style="width: 16px; height: 1px; left: 0px; top: 6.30px; position: absolute; opacity: 0.60; background: #B8945A"></div>
      <div style="left: 22px; top: -1px; position: absolute; color: #B8945A; font-size: 8.50px; font-weight: 500; text-transform: uppercase; letter-spacing: 1.19px;">Смета проекта</div>
    </div>

    <h2 class="ligron-serif-title" style="font-size: 26px; line-height: 28px; margin: 0 0 20px 0;">
      Детальный расчёт<br>по всем изделиям
    </h2>

    <table class="estimate-table">
      <thead>
      <tr class="estimate-table-th">
        <th style="text-align: left; padding-left: 15px;">Услуга / Работа</th>
        <th style="text-align: center; width: 100px;">Кол-во</th>
        <th style="text-align: right; width: 130px;">Цена</th>
        <th style="text-align: right; width: 130px; padding-right: 15px;">Итого</th>
      </tr>
      </thead>
      <tbody>
      @foreach ($order->sections as $index => $section)
        <tr class="estimate-section-header">
          <td colspan="3" style="text-align: left;">
            0{{ $index + 1 }} — {{ $section->title }}
            @php
              $stoneName = collect($section->specs ?? [])->firstWhere('key', 'stone_name')['value'] ?? null;
            @endphp
            @if($stoneName)
              · {{ $stoneName }}
            @endif
          </td>
          <td class="estimate-section-price" style="padding-right: 15px;">
            {{ number_format($section->total_price, 0, '.', ' ') }} {{ $currencySymbol }}
          </td>
        </tr>

        @foreach ($section->items as $item)
          <tr class="estimate-row">
            <td class="estimate-cell-name">{{ $item->name }}</td>
            <td class="estimate-cell-qty">{{ (float)$item->quantity }} {{ $item->unit }}</td>
            <td class="estimate-cell-price">{{ number_format($item->price, 0, '.', ' ') }} {{ $currencySymbol }}</td>
            <td class="estimate-cell-total">{{ number_format($item->total, 0, '.', ' ') }} {{ $currencySymbol }}</td>
          </tr>
        @endforeach
      @endforeach
      </tbody>
    </table>

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
        <div class="grand-total-label">Общая сумма · {{ $sectionsWord }}</div>
        <div class="grand-total-desc">Все работы, материалы, монтаж и доставка включены в стоимость</div>
      </div>
      <div class="grand-total-right">
        {{ number_format($order->grand_total, 0, '.', ' ') }} {{ $currencySymbol }}
      </div>
    </div>
  </div>

  @include('valerie-stone::pdf.partials.footer', ['pageNum' => 3])
</div>
