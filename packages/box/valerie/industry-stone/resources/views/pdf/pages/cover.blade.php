<!-- Титульный лист -->
<div class="page page-cover">

  <div style="align-self: stretch; height: 0.80px; background: linear-gradient(90deg, #B8945A 0%, rgba(184, 148, 90, 0) 70%)"></div>

  <div class="cover-photo-container">
    <div class="cover-photo-bg" style="background-image: url('{{ config('nicole.company.cover_image', '/pdf/cover.jpg') }}');"></div>
    <div class="cover-photo-gradient"></div>
  </div>

  <div class="cover-content">
    <div class="cover-top-line"></div>

    <div class="cover-brand-header">{{ config('nicole.company.name') }}</div>

    <h1 class="cover-title">
      Коммерческое<br>
      <span class="italic-gold">предложение</span>
    </h1>

    <div class="cover-gold-divider"></div>

    <div class="cover-meta-table">
      <div class="cover-meta-cell">
        <div class="cover-meta-label">Подготовлено для</div>
        <div class="cover-meta-value">{{ $order->customer?->full_name ?? 'Иванова Анна Сергеевна' }}</div>
        <div class="cover-meta-subvalue">г. {{ $order->customer?->city ?? 'Москва' }}</div>
      </div>
      <div class="cover-meta-cell" style="text-align: right;">
        <div class="cover-meta-label">Документ</div>
        <div class="cover-meta-value">КП № {{ $order->kp_number }}</div>
        <div class="cover-meta-subvalue">Дата: {{ $order->created_at ? $order->created_at->format('d.m.Y') : date('d.m.Y') }} г.</div>
      </div>
    </div>

    <div class="cover-price-badge">
      <span class="cover-price-label">Общая стоимость</span>
      <!-- Заменяем ₽ на динамический $currencySymbol -->
      <span class="cover-price-value">{{ number_format($order->grand_total, 0, '.', ' ') }} {{ $currencySymbol }}</span>
    </div>

    <div class="cover-page-indicator">
      {{ $order->sections->count() }} {{ trans_choice('изделие|изделия|изделий', $order->sections->count(), [], 'ru') }} · 1 / 4
    </div>
  </div>
</div>
