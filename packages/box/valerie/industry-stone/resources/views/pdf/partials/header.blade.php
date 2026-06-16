<div class="page-header">
  <!-- Выводим имя компании динамически -->
  <div class="header-cell-left">{{ config('nicole.company.name') }}</div>
  <div class="header-cell-right">{{ $subtitle ?? 'Коммерческое предложение' }} · КП № {{ $order->kp_number }}</div>
</div>
