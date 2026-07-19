<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('legal_title') — LaBioTrack</title>
    <link rel="icon" type="image/png" href="{{ asset('labiotrack-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#F4F7F6; color:#1A2332; }
        .legal-wrap { max-width:820px; margin:0 auto; padding:2.5rem 1.25rem 4rem; }
        .legal-card { background:#fff; border:1px solid #e5e9ef; border-radius:16px; padding:2.5rem; box-shadow:0 10px 40px rgba(0,0,0,.05); }
        .legal-card h1 { color:#1B6B3A; font-size:1.6rem; font-weight:700; }
        .legal-card h2 { color:#1B6B3A; font-size:1.1rem; font-weight:600; margin-top:1.8rem; }
        .legal-card p, .legal-card li { font-size:.92rem; line-height:1.65; color:#374151; }
        .legal-meta { font-size:.8rem; color:#6b7280; }
        .todo { background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:.1rem .4rem; font-size:.85rem; color:#92400e; }
        .legal-nav a { font-size:.85rem; color:#1B6B3A; text-decoration:none; margin-right:1rem; }
    </style>
</head>
<body>
<div class="legal-wrap">
    <div class="mb-3 legal-nav">
        <a href="{{ route('login') }}"><i class="bi bi-arrow-left me-1"></i>{{ __('legal.back_home') }}</a>
        <a href="{{ route('legal.mentions') }}">{{ __('legal.nav_mentions') }}</a>
        <a href="{{ route('legal.privacy') }}">{{ __('legal.nav_privacy') }}</a>
        <a href="{{ route('legal.cgu') }}">{{ __('legal.nav_cgu') }}</a>
    </div>
    <div class="legal-card">
        @yield('legal_content')
        <hr class="my-4">
        <p class="legal-meta">{{ __('legal.last_updated', ['date' => now()->translatedFormat('F Y')]) }}</p>
    </div>
</div>
</body>
</html>
