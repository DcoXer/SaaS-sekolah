<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $seo      = $page['props']['seo'] ?? [];
            $siteName = $seo['name']        ?? config('app.name');
            $siteDesc = $seo['description'] ?? '';
            $siteUrl  = $seo['website']     ?? url('/');
            $logoUrl  = $seo['logo_url']    ?? '';
            $address  = $seo['address']     ?? '';
            $phone    = $seo['phone']       ?? '';
            $email    = $seo['email']       ?? '';
            $npsn     = $seo['npsn']        ?? '';
            $canonical = url()->current();
        @endphp

        <!-- Primary -->
        <title inertia>{{ $siteName }}</title>
        <meta name="description" content="{{ $siteDesc }}">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ $canonical }}">

        <!-- Open Graph -->
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $siteName }}">
        <meta property="og:description" content="{{ $siteDesc }}">
        <meta property="og:url" content="{{ $canonical }}">
        @if($logoUrl)
        <meta property="og:image" content="{{ $logoUrl }}">
        <meta property="og:image:alt" content="Logo {{ $siteName }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        @endif
        <meta property="og:locale" content="id_ID">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="{{ $siteName }}">
        <meta name="twitter:description" content="{{ $siteDesc }}">
        @if($logoUrl)
        <meta name="twitter:image" content="{{ $logoUrl }}">
        <meta name="twitter:image:alt" content="Logo {{ $siteName }}">
        @endif

        <!-- App / theme -->
        <meta name="theme-color" content="#10b981">
        <meta name="application-name" content="{{ $siteName }}">
        <meta name="author" content="{{ $siteName }}">

        <!-- PWA manifest (dinamis mengikuti logo sekolah) -->
        <link rel="manifest" href="/manifest.json">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">
        <meta name="mobile-web-app-capable" content="yes">
        @if($logoUrl)
            <link rel="icon" type="image/png" href="{{ $logoUrl }}">
            <link rel="apple-touch-icon" href="{{ $logoUrl }}">
        @else
            <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">
            <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
        @endif

        <!-- JSON-LD: School + WebSite -->
        @php
            $schoolLd = [
                '@context'   => 'https://schema.org',
                '@type'      => 'School',
                '@id'        => url('/') . '/#school',
                'name'       => $siteName,
                'url'        => url('/'),
                'inLanguage' => 'id',
            ];
            if ($siteDesc) $schoolLd['description'] = $siteDesc;
            if ($npsn)     $schoolLd['identifier']  = $npsn;
            if ($address)  $schoolLd['address']     = ['@type' => 'PostalAddress', 'streetAddress' => $address, 'addressCountry' => 'ID'];
            if ($phone)    $schoolLd['telephone']   = $phone;
            if ($email)    $schoolLd['email']       = $email;
            if ($logoUrl) {
                $schoolLd['logo']  = ['@type' => 'ImageObject', 'url' => $logoUrl];
                $schoolLd['image'] = $logoUrl;
            }

            $websiteLd = [
                '@context'   => 'https://schema.org',
                '@type'      => 'WebSite',
                '@id'        => url('/') . '/#website',
                'name'       => $siteName,
                'url'        => url('/'),
                'inLanguage' => 'id',
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode([$schoolLd, $websiteLd], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
