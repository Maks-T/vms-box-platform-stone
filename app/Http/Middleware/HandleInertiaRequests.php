<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
  /**
   * The root template that's loaded on the first page visit.
   *
   * @see https://inertiajs.com/server-side-setup#root-template
   *
   * @var string
   */
  protected $rootView = 'app';

  /**
   * Determines the current asset version.
   *
   * @see https://inertiajs.com/asset-versioning
   */
  public function version(Request $request): ?string
  {
    return parent::version($request);
  }

  /**
   * Define the props that are shared by default.
   *
   * @see https://inertiajs.com/shared-data
   *
   * @return array<string, mixed>
   */
  public function share(Request $request): array
  {
    $employee = $request->user(); // $request->user('web')

    return [
      ...parent::share($request),
      'auth' => [
        'client' => null, // для авторизации клиентов
        'employee' => $employee ? [
          'id' => $employee->id,
          'name' => $employee->name,
          'email' => $employee->email,
          'roles' => method_exists($employee, 'getRoleNames')
            ? $employee->getRoleNames()->toArray()
            : [],
        ] : null,
      ],
      'locale' => app()->getLocale()
      //
    ];

  }
}
