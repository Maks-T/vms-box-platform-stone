<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
  protected $fillable = [
    'first_name',
    'last_name',
    'middle_name',
    'phone',
    'phone_normalized',
    'email',
    'address',
    'admin_notes',
    'last_ip',
  ];

  protected static function booted(): void
  {
    static::saving(function (Customer $customer) {
      if ($customer->isDirty('phone') && !empty($customer->phone)) {
        $customer->phone_normalized = preg_replace('/[^0-9]/', '', (string)$customer->phone);
      }
    });
  }

  public function orders(): HasMany
  {
    return $this->hasMany(Order::class, 'customer_id');
  }

  public function getFullNameAttribute(): string
  {
    return trim("{$this->last_name} {$this->first_name} {$this->middle_name}");
  }
}
