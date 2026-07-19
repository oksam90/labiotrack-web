<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth_ui.sign_in') }} — LaBioTrack</title>
    <link rel="icon" type="image/png" href="{{ asset('labiotrack-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Fond image + overlay sombre pour garantir le contraste du texte (WCAG AA) */
        body { position:relative; background-image: url('/images/labiotrack_logo.svg'); background-size: cover; background-position: center; background-attachment: fixed; min-height: 100vh; display: flex; align-items: center; }
        body::before { content:''; position:fixed; inset:0; background:rgba(15,23,32,.62); z-index:0; }
        .container { position:relative; z-index:1; }
        /* Carte translucide foncée → texte blanc lisible quel que soit le fond */
        .login-card { background:rgba(20,28,40,.78); border: 1px solid rgba(255, 255, 255, 0.18); color: #fff; border-radius:16px; padding:2.5rem; box-shadow:0 20px 60px rgba(0,0,0,.4); max-width:420px; width:100%; }
        .login-card::placeholder{color: rgba(255, 255, 255, 0.7);}
        .form-control { border-radius:8px; border:1.5px solid #e5e9ef; padding:.7rem 1rem; }
        .form-control:focus { border-color:#2E8B57; box-shadow:0 0 0 3px rgba(46,139,87,.12); }
        .btn-login { background:linear-gradient(135deg,#1B6B3A,#2E8B57); border:none; border-radius:8px; padding:.75rem; font-weight:600; width:100%; transition:filter .15s, transform .15s; }
        .btn-login:hover { filter:brightness(1.1); transform:translateY(-1px); }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="login-card">
                <div class="text-center">
                    <h5 class="fw-bold mb-1" style="color:#e5e9ef;">{{ __('auth_ui.login_title') }}</h5>
                </div>

                @if(session('warning'))
                <div class="alert alert-warning py-2 mb-3" style="font-size:.85rem;">
                    <i class="bi bi-clock-history me-1"></i>{{ session('warning') }}
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem;">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
                </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">{{ __('auth_ui.email') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope" style="color:#1B6B3A;"></i></span>
                            <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="{{ __('auth_ui.email_placeholder') }}" value="{{ old('email') }}" required autofocus style="border-left:none;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">{{ __('auth_ui.password') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock" style="color:#1B6B3A;"></i></span>
                            <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="{{ __('auth_ui.password_placeholder') }}" required>
                        </div>
                    </div>
                    <div class="mb-4 d-flex align-items-center justify-content-between">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember" style="font-size:.85rem;">{{ __('auth_ui.remember_me') }}</label>
                        </div>
                        <a href="{{ route('password.request') }}" style="font-size:.85rem;color:#D4A017;text-decoration:none;">{{ __('auth_ui.forgot_password') }}</a>
                    </div>
                    <button type="submit" class="btn btn-login text-white">
                        <i class="bi bi-box-arrow-in-right me-2"></i>{{ __('auth_ui.sign_in') }}
                    </button>
                </form>

                <div class="text-center mt-4" style="font-size:.75rem;">
                    <a href="{{ route('legal.mentions') }}" style="color:rgba(255,255,255,.7);text-decoration:none;">{{ __('legal.nav_mentions') }}</a>
                    <span style="color:rgba(255,255,255,.4);">·</span>
                    <a href="{{ route('legal.privacy') }}" style="color:rgba(255,255,255,.7);text-decoration:none;">{{ __('legal.nav_privacy') }}</a>
                    <span style="color:rgba(255,255,255,.4);">·</span>
                    <a href="{{ route('legal.cgu') }}" style="color:rgba(255,255,255,.7);text-decoration:none;">{{ __('legal.nav_cgu') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
