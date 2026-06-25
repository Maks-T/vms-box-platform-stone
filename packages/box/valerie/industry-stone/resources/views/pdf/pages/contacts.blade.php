@php
  /** @var \Nicole\Box\Core\Models\Order $order */
    $validUntil = $order->created_at
        ? $order->created_at->addDays(30)->format('d.m.Y')
        : date('d.m.Y', strtotime('+30 days'));
@endphp

<div class="page page-dark">
  <div class="top-gradient-line"></div>
  @include('valerie-stone::pdf.partials.header', ['subtitle' => 'Готовы оформить заказ?'])

  <div class="page-content">
    <div style="align-self: stretch; height: 24.93px; position: relative">
      <div style="width: 699.88px; padding-bottom: 0.59px; left: 0px; top: -1px; position: absolute; opacity: 0.70; display: table;">
        <div style="display: table-cell; color: #B8945A; font-size: 8.50px; font-family: Jost; font-weight: 500; text-transform: uppercase; line-height: 13.60px; letter-spacing: 1.36px;">
          12 · Следующий шаг
        </div>
      </div>
    </div>

    <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 40px; font-weight: 300; line-height: 44px; margin: 0 0 15px 0; color: #ffffff;">
      Готовы<br>
      <span style="color: #D4B483; font-style: italic;">оформить заказ?</span>
    </h2>

    <div class="dark-divider"></div>

    <div class="steps-container">
      <div class="step-card">
        <div class="step-num">1</div>
        <div class="step-content">
          <div class="step-title">Подтвердите КП</div>
          <div class="step-desc">Напишите или позвоните менеджеру — уточним детали и зафиксируем заказ</div>
        </div>
      </div>

      <div class="step-card">
        <div class="step-num">2</div>
        <div class="step-content">
          <div class="step-title">Внесите предоплату 50%</div>
          <div class="step-desc">После получения предоплаты немедленно запускаем производство</div>
        </div>
      </div>

      <div class="step-card">
        <div class="step-num">3</div>
        <div class="step-content">
          <div class="step-title">Согласуйте дату замера</div>
          <div class="step-desc">Выберите удобный день — выедем в течение 1–2 рабочих дней</div>
        </div>
      </div>
    </div>

    <div class="manager-card" style="padding: 20px 30px;">
      <div class="manager-info" style="padding-left: 0;">
        <div class="manager-post">Ваш персональный менеджер</div>
        <div class="manager-name">
          {{ $order->manager?->name ?? 'Елена Петрова' }}
        </div>

        <ul class="manager-contacts-list">
          <li><span>Телефон:</span> {{ $order->manager?->phone ?? config('nicole.company.phone') }}</li>
          <li><span>Email:</span> {{ $order->manager?->email ?? config('nicole.company.email') }}</li>
          <li><span>Поддержка:</span> Telegram · WhatsApp · Viber</li>
          <li><span>График:</span> Пн–Пт 10:00–20:00</li>
        </ul>
      </div>
    </div>

    <div class="closing-meta">
      <div class="closing-cell-left">
        <div class="closing-label-left">Срок действия КП</div>
        <div class="closing-value-left">до {{ $validUntil }} года · 30 дней</div>
      </div>

      <div class="closing-cell-right">
        <div class="closing-label-right">Общая сумма заказа</div>
        <div class="closing-value-right" style="font-family: 'DejaVu Sans';">
          {{ number_format($order->grand_total, 0, '.', ' ') }} {{ $currencySymbol }}
        </div>
      </div>
    </div>
  </div>

  @include('valerie-stone::pdf.partials.footer', ['pageNum' => 4])
</div>
