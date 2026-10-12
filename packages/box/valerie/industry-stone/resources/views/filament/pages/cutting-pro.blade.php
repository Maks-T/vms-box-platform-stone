<x-filament-panels::page>
    <script>
        window.initialData = {
            apiUrl: '{{ url('/api/v1') }}',
            baseUrl: '{{ url('/') }}',
        };
    </script>
    <div id="cuttingProAppRoot" class="w-full min-h-[calc(100vh-10rem)]"></div>
    <script src="{{ asset('cutting-pro/embed.js') }}?v={{ file_exists(public_path('cutting-pro/embed.js')) ? filemtime(public_path('cutting-pro/embed.js')) : time() }}" data-target="cuttingProAppRoot"></script>
</x-filament-panels::page>