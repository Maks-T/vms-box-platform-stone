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
            @if ($section->hasMedia('drawing'))
              <img src="{{ $section->getFirstMediaPath('drawing') }}" alt="{{ $section->title }}">
            @else
              <img src="https://placehold.co/400x300" alt="Нет чертежа">
            @endif
          </div>

          <div class="section-body-right">
            <table class="specs-table">
              @foreach ($section->specs ?? [] as $spec)
                <tr>
                  <td class="spec-label">{{ $spec['label'] }}</td>
                  <td class="spec-value">{{ $spec['value'] }}</td>
                </tr>
              @endforeach
            </table>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  @include('valerie-stone::pdf.partials.footer', ['pageNum' => 2])
</div>
