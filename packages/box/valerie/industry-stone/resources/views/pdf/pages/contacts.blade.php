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
    <div class="contacts-header-container">
      <div class="contacts-header-table">
        <div class="contacts-header-cell">
          · Следующий шаг
        </div>
      </div>
    </div>

    <h2 class="contacts-title">
      Готовы<br>
      <span class="gold-italic">оформить заказ?</span>
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

    @if ($order->manager)
      <div class="manager-card">
        <div class="manager-info">
          <div class="manager-post">Ваш персональный менеджер</div>
          <div class="manager-name">
            {{ $order->manager->name }}
          </div>

          <ul class="manager-contacts-list">
            @if ($order->manager->phone || config('nicole.company.phone'))
              <li><span>Телефон:</span> {{ $order->manager->phone ?? config('nicole.company.phone') }}</li>
            @endif
            @if ($order->manager->email || config('nicole.company.email'))
              <li><span>Email:</span> {{ $order->manager->email ?? config('nicole.company.email') }}</li>
            @endif
            <li><span>Поддержка:</span> Telegram · WhatsApp · Viber</li>
            <li><span>График:</span> Пн–Пт 10:00–20:00</li>
          </ul>
        </div>
      </div>
    @elseif (config('nicole.company.phone') || config('nicole.company.email'))
      <div class="manager-card">
        <div class="manager-info">
          <div class="manager-post">Контакты компании</div>
          <div class="manager-name">
            {{ config('nicole.company.name') }}
          </div>

          <ul class="manager-contacts-list">
            @if (config('nicole.company.phone'))
              <li><span>Телефон:</span> {{ config('nicole.company.phone') }}</li>
            @endif
            @if (config('nicole.company.email'))
              <li><span>Email:</span> {{ config('nicole.company.email') }}</li>
            @endif
            <li><span>Поддержка:</span> Telegram · WhatsApp</li>
            <li><span>График:</span> Пн–Пт 10:00–20:00</li>
          </ul>
        </div>
      </div>
    @endif

    <div class="closing-meta">
      <div class="closing-cell-left">
        <div class="closing-label-left">Срок действия КП</div>
        <div class="closing-value-left">до {{ $validUntil }} года · 30 дней</div>
      </div>

      <div class="closing-cell-right">
        <div class="closing-label-right">Общая сумма заказа</div>
        <div class="closing-value-right">
          {{ number_format($order->grand_total, 0, '.', ' ') }} {{ $currencySymbol }}
        </div>
      </div>
    </div>
  </div>

  @include('valerie-stone::pdf.partials.footer', ['pageNum' => 4])
</div>
