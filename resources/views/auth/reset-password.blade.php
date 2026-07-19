<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth_ui.reset_title') }} — LaBioTrack</title>
    <link rel="icon" type="image/png" href="{{ asset('labiotrack-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#F4F7F6; min-height:100vh; display:flex; align-items:center; }
        .auth-card { background:#fff; border:1px solid #e5e9ef; border-radius:16px; padding:2.5rem; box-shadow:0 20px 60px rgba(0,0,0,.08); max-width:420px; width:100%; }
        .form-control { border-radius:8px; border:1.5px solid #e5e9ef; padding:.7rem 1rem; }
        .form-control:focus { border-color:#2E8B57; box-shadow:0 0 0 3px rgba(46,139,87,.12); }
        .btn-primary-lbt { background:linear-gradient(135deg,#1B6B3A,#2E8B57); border:none; border-radius:8px; padding:.75rem; font-weight:600; width:100%; color:#fff; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="auth-card mx-auto">
                <h5 class="fw-bold mb-3" style="color:#1B6B3A;">
                    <i class="bi bi-shield-lock me-1"></i>{{ __('auth_ui.reset_title') }}
                </h5>

                @if($errors->any())
                <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem;">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
                </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">{{ __('auth_ui.email') }}</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">{{ __('auth_ui.new_password') }}</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">{{ __('auth_ui.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn-primary-lbt mb-3">
                        <i class="bi bi-check-circle me-2"></i>{{ __('auth_ui.reset_submit') }}
                    </button>
                </form>

                <a href="{{ route('login') }}" class="text-decoration-none" style="font-size:.85rem;color:#1B6B3A;">
                    <i class="bi bi-arrow-left me-1"></i>{{ __('auth_ui.back_to_login') }}
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
