<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nicole\Box\Core\Traits\HasNicoleMedia;
use Spatie\MediaLibrary\HasMedia;

class OrderSection extends Model implements HasMedia
{
  use HasNicoleMedia;

  protected $fillable = [
    'order_id',
    'item_id',
    'title',
    'total_price',
    'specs',
  ];

  protected function casts(): array
  {
    return [
      'total_price' => 'float',
      'specs' => 'array',
    ];
  }

  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class, 'order_id');
  }

  /**
   * Связь со всеми позициями сметы, относящимися именно к этой секции
   */
  public function items(): HasMany
  {
    return $this->hasMany(OrderItem::class, 'order_section_id');
  }

  /**
   * Регистрация коллекции чертежей в медиабиблиотеке
   */
  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('drawing')->singleFile();
  }
}
