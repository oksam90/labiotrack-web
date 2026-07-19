<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — LaBioTrack</title>
    <link rel="icon" type="image/png" href="{{ asset('labiotrack-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#F4F7F6; color:#1A2332; min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:'Segoe UI',system-ui,sans-serif; }
        .err-card { background:#fff; border:1px solid #e5e9ef; border-radius:16px; padding:3rem 2.5rem; box-shadow:0 20px 60px rgba(0,0,0,.06); max-width:480px; width:100%; text-align:center; }
        .err-code { font-size:4rem; font-weight:800; line-height:1; color:#1B6B3A; }
        .err-icon { font-size:2.4rem; color:#D4A017; margin-bottom:.5rem; }
        .err-title { font-size:1.15rem; font-weight:700; margin:.6rem 0 .3rem; }
        .err-msg { color:#6b7280; font-size:.92rem; }
        .btn-lbt { background:linear-gradient(135deg,#1B6B3A,#2E8B57); border:none; border-radius:8px; padding:.6rem 1.4rem; font-weight:600; color:#fff; }
    </style>
</head>
<body>
    <div class="err-card">
        <div class="err-icon"><i class="bi @yield('icon', 'bi-exclamation-triangle')"></i></div>
        <div class="err-code">@yield('code')</div>
        <div class="err-title">@yield('title')</div>
        <p class="err-msg">@yield('message')</p>
        <a href="{{ url('/') }}" class="btn-lbt mt-3 d-inline-block">
            <i class="bi bi-house me-1"></i>{{ __('errors.back_home') }}
        </a>
    </div>
</body>
</html>
