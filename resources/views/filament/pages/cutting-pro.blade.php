<x-filament-panels::page class="p-0 -m-6 sm:-m-8">
    <div wire:ignore id="cuttingProAppRoot" class="w-full min-h-[calc(100vh-4.5rem)]"></div>

    <script
        src="{{ asset('cutting-pro/embed.js') }}?v={{ file_exists(public_path('cutting-pro/embed.js')) ? filemtime(public_path('cutting-pro/embed.js')) : time() }}"
        data-target="cuttingProAppRoot"
        defer
    ></script>
</x-filament-panels::page>