<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nicole\Box\Core\Traits\HasExternalCode;

class Order extends Model
{
  use HasExternalCode;

  protected $fillable = [
    'external_code',
    'customer_id',
    'grand_total',
    'currency',
    'locale',
    'status_id',
    'customer_comment',
    'manager_comment',
    'calculator_state',
    'manager_id',
  ];

  protected function casts(): array
  {
    return [
      'grand_total' => 'float',
      'calculator_state' => 'array',
    ];
  }

  public function customer(): BelongsTo
  {
    return $this->belongsTo(Customer::class, 'customer_id');
  }

  public function status(): BelongsTo
  {
    return $this->belongsTo(OrderStatus::class, 'status_id');
  }

  /**
   * Связь со всеми секциями (изделиями) в рамках этого заказа
   */
  public function sections(): HasMany
  {
    return $this->hasMany(OrderSection::class, 'order_id');
  }

  /**
   * Связь со всеми детальными строками сметы этого заказа
   */
  public function items(): HasMany
  {
    return $this->hasMany(OrderItem::class, 'order_id');
  }

  public function manager(): BelongsTo
  {
    $userModel = config('nicole.models.staff', \App\Models\User::class);
    return $this->belongsTo($userModel, 'manager_id');
  }

  protected static function booted(): void
  {
    static::creating(function (Order $order) {
      if (empty($order->code)) {
        $datePrefix = date('ymd');
        $todayOrdersCount = self::whereDate('created_at', today())->count();
        $sequence = str_pad((string)($todayOrdersCount + 1), 4, '0', STR_PAD_LEFT);

        
        $order->code = "V-{$datePrefix}-{$sequence}";
      }
    });
  }

  public function getKpNumberAttribute(): string
  {
    return $this->code;
  }
}
