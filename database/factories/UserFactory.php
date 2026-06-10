<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
  /**
   * Имя модели, соответствующей фабрике.
   */
  protected $model = User::class;

  /**
   * Определение дефолтных полей.
   */
  public function definition(): array
  {
    return [
      'name' => $this->faker->name(),
      'email' => $this->faker->unique()->safeEmail(),
      'email_verified_at' => now(),
      'password' => Hash::make('password'), // дефолтный пароль
      'remember_token' => Str::random(10),
    ];
  }

}
