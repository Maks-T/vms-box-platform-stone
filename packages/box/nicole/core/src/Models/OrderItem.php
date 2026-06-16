<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
  protected $fillable = [
    'order_id',
    'order_section_id',
    'product_variant_id',
    'name',
    'quantity',
    'unit',
    'price',
    'total',
    'group_id',
    'group_title',
  ];

  protected function casts(): array
  {
    return [
      'quantity' => 'float',
      'price' => 'float',
      'total' => 'float',
    ];
  }

  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class, 'order_id');
  }

  /**
   * Связь с родительским изделием (секцией) заказа
   */
  public function section(): BelongsTo
  {
    return $this->belongsTo(OrderSection::class, 'order_section_id');
  }

  public function variant(): BelongsTo
  {
    return $this->belongsTo(ProductVariant::class, 'product_variant_id');
  }
}
