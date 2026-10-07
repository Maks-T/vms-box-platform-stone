{{-- resources/views/partials/seo.blade.php --}}
@php
  $title = $seo['title'] ?? 'Кварцевый камень купить в Москве | Каталог QuartzMaster';
  $description = $seo['description'] ?? 'Изделия из кварцевого камня и широкоформатной керамики от производителя в Москве: столешницы, подоконники, ступени.';
  $keywords = $seo['keywords'] ?? 'кварцевый камень, кварцевый агломерат, столешницы из кварца, подоконники, quartzmaster, москва';

  $image = $seo['og_image'] ?? asset('images/og-default.webp');
  $url = url()->current();
  $siteName = 'QuartzMaster';
@endphp

  <!-- Базовые SEO теги -->
<title inertia>{{ $title }}</title>
<meta name="description" content="{{ $description }}" inertia>
<meta name="keywords" content="{{ $keywords }}" inertia>

<!-- Open Graph (Telegram, WhatsApp, VK, Facebook) -->
<meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}" inertia>
<meta property="og:site_name" content="{{ $siteName }}" inertia>
<meta property="og:title" content="{{ $title }}" inertia>
<meta property="og:description" content="{{ $description }}" inertia>
<meta property="og:url" content="{{ $url }}" inertia>

<!-- Изображение Open Graph -->
<meta property="og:image" content="{{ $image }}" inertia>
<meta property="og:image:type" content="image/webp" inertia>
<meta property="og:image:width" content="1200" inertia>
<meta property="og:image:height" content="630" inertia>

<!-- Twitter Cards (Telegram/Discord) -->
<meta name="twitter:card" content="summary_large_image" inertia>
<meta name="twitter:title" content="{{ $title }}" inertia>
<meta name="twitter:description" content="{{ $description }}" inertia>
<meta name="twitter:image" content="{{ $image }}" inertia>
