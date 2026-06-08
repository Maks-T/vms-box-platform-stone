<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
  use HasFactory, Notifiable, HasRoles;

  protected $fillable =[
    'name',
    'email',
    'password',
  ];

  protected $hidden =[
    'password',
    'remember_token',
  ];

  public function canAccessPanel(Panel $panel): bool
  {
    // доступ есть у всех активных сотрудников
    return true;
  }

  protected function casts(): array
  {
    return[
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }
}