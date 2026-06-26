@php
  /** @var \Nicole\Box\Core\Models\Order $order */

  $coverPath = public_path(config('nicole.company.cover_image', 'pdf/cover.jpg'));
  $coverBase64 = '';
  if (file_exists($coverPath)) {
      $coverBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($coverPath));
  }
@endphp

<div class="page page-cover">

  <div class="cover-brand-top-line"></div>

  <div class="cover-photo-container">
    @if ($coverBase64)
      <img src="{{ $coverBase64 }}" alt="Cover" class="cover-photo-img">
    @endif
    <div class="cover-photo-gradient"></div>
  </div>

  <div class="cover-content">
    <div class="cover-top-line"></div>

    <div class="cover-brand-header">
      {{ strtoupper(config('nicole.company.name', 'LIGRON')) }}
    </div>

    <!-- Заголовок предложения -->
    <h1 class="cover-title">
      Коммерческое<br>
      <span class="italic-gold">предложение</span>
    </h1>

    <div class="cover-gold-divider"></div>

    <div class="cover-meta-table">
      @if ($order->customer)
        <div class="cover-meta-cell">
          <div class="cover-meta-label">Подготовлено для</div>
          <div class="cover-meta-value">{{ $order->customer->full_name }}</div>
          @if ($order->customer->city)
            <div class="cover-meta-subvalue">г. {{ $order->customer->city }}</div>
          @endif
        </div>
      @endif

      <div class="cover-meta-cell cover-meta-cell-right" @if (!$order->customer) style="width: 100%; text-align: left;" @endif>
        <div class="cover-meta-label">Документ</div>
        <div class="cover-meta-value">КП № {{ $order->code }}</div>
        <div class="cover-meta-subvalue">
          Дата: {{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->translatedFormat('j F Y') : date('d.m.Y') }} г.
        </div>
      </div>
    </div>

    <div class="cover-footer-row">
      <div class="cover-footer-left">
        <div class="cover-price-badge">
          <span class="cover-price-label">Общая стоимость</span>
          <span class="cover-price-value">
            {{ number_format($order->grand_total, 0, '.', ' ') }} {{ $currencySymbol }}
          </span>
        </div>
      </div>
      <div class="cover-footer-right">
        <div class="cover-page-indicator">
          {{ $order->sections->count() }} {{ trans_choice('изделие|изделия|изделий', $order->sections->count(), [], 'ru') }} · 1 / {{ $totalPages ?? 16 }}
        </div>
      </div>
    </div>

  </div>
</div>
