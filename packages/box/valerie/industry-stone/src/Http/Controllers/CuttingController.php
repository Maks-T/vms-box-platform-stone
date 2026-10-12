<?php

declare(strict_types=1);

namespace Valerie\Box\IndustryStone\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер публичной страницы раскроя слэбов камня.
 *
 * @since 2026-10-12
 */
class CuttingController
{
    public function show(Request $request): Response
    {
        $widgetSlug = 'cutting-pro';
        $baseUrl = rtrim((string) config('app.url', url('/')), '/');
        $user = auth()->user();

        $initialData = [
            'apiUrl' => "{$baseUrl}/api/v1",
            'baseUrl' => $baseUrl,
            'assetsUrl' => "{$baseUrl}/{$widgetSlug}/",
            'channel' => 'widget',
            'widget' => $widgetSlug,
            'auth' => [
                'client' => null,
                'employee' => $user ? [
                    'id' => (int) $user->id,
                    'name' => (string) $user->name,
                    'email' => (string) $user->email,
                    'roles' => method_exists($user, 'getRoleNames')
                        ? $user->getRoleNames()->values()->toArray()
                        : (isset($user->roles) ? collect($user->roles)->pluck('name')->filter()->values()->toArray() : []),
                ] : null,
            ],
        ];

        $embedUrl = $this->resolveEmbedUrl($baseUrl, $widgetSlug);

        return Inertia::render('Cutting/Show', [
            'embedUrl' => $embedUrl,
            'widgetSlug' => $widgetSlug,
            'initialData' => $initialData,
        ]);
    }

    protected function resolveEmbedUrl(string $baseUrl, string $widgetSlug): string
    {
        if (file_exists(public_path("{$widgetSlug}/embed.js"))) {
            return "{$baseUrl}/{$widgetSlug}/embed.js";
        }

        if (file_exists(public_path("storage/{$widgetSlug}/embed.js"))) {
            return "{$baseUrl}/storage/{$widgetSlug}/embed.js";
        }

        return "{$baseUrl}/{$widgetSlug}/embed.js";
    }
}