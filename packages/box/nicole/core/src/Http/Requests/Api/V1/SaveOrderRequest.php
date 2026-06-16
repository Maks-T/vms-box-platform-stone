<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SaveOrderRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'customer.name' => ['nullable', 'string', 'max:255'],
      'customer.phone' => ['required', 'string', 'max:50'],
      'customer.email' => ['nullable', 'email', 'max:255'],
      'customer.city' => ['nullable', 'string', 'max:255'],
      'customer.address' => ['nullable', 'string'],

      'manager_id' => ['nullable', 'integer', 'exists:users,id'],
      'grand_total' => ['required', 'numeric', 'min:0'],
      'currency' => ['required', 'string', 'max:3'],

      'calculator_state.type' => ['required', 'string', 'max:50'],
      'calculator_state.raw_json' => ['required', 'string'],

      // Секции
      'items' => ['required', 'array', 'min:1'],
      'items.*.id' => ['required', 'string', 'max:50'],
      'items.*.title' => ['required', 'string', 'max:255'],
      'items.*.total_price' => ['required', 'numeric', 'min:0'],
      'items.*.draw' => ['nullable', 'string'], // base64 холста
      'items.*.specs' => ['nullable', 'array'],
      'items.*.specs.*.key' => ['required_with:items.*.specs', 'string', 'max:50'],
      'items.*.specs.*.label' => ['required_with:items.*.specs', 'string', 'max:255'],
      'items.*.specs.*.value' => ['nullable', 'string'],

      // Позиции сметы внутри секций
      'items.*.estimate_groups' => ['required', 'array', 'min:1'],
      'items.*.estimate_groups.*.id' => ['required', 'string', 'max:50'],
      'items.*.estimate_groups.*.title' => ['required', 'string', 'max:255'],
      'items.*.estimate_groups.*.total' => ['required', 'numeric'],
      'items.*.estimate_groups.*.items' => ['required', 'array'],
      'items.*.estimate_groups.*.items.*.product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
      'items.*.estimate_groups.*.items.*.name' => ['required', 'string', 'max:255'],
      'items.*.estimate_groups.*.items.*.quantity' => ['required', 'numeric'],
      'items.*.estimate_groups.*.items.*.unit' => ['nullable', 'string', 'max:20'],
      'items.*.estimate_groups.*.items.*.price' => ['required', 'numeric'],
      'items.*.estimate_groups.*.items.*.total' => ['required', 'numeric'],
    ];
  }
}
