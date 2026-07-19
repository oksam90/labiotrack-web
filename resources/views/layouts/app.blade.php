<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('nav.brand_platform')) — LaBioTrack</title>

    <!-- Bootstrap 5 + Icons — bundlés via Vite (plus de CDN) -->
    <link rel="icon" type="image/png" href="{{ asset('labiotrack-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Chart.js auto-hébergé (script classique → window.Chart dispo pour les blocs inline) -->
    <script src="{{ asset('vendor/js/chart.umd.min.js') }}"></script>

    {{-- Design system LaBioTrack externalisé → resources/css/labiotrack.css (bundlé via @vite) --}}
    @stack('styles')
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-virus2 text-success"></i>
            <div>
                <h6 class="mb-0">{{ __('nav.brand_platform') }}</h6>
                <h4 class="mb-0">LaBio<span style="color:var(--secondary)">Track</span></h4>
            </div>
        </div>
        @if(isset($currentTenant) && $currentTenant)
        <div class="mt-2 pt-2 border-top border-secondary opacity-75" style="font-size:.75rem;">
            <i class="bi bi-hospital me-1"></i>
            {{ $currentTenant->nom }}
        </div>
        @elseif(Auth::user()->etablissement_id)
        <div class="mt-2 pt-2 border-top border-secondary opacity-75" style="font-size:.75rem;">
            <i class="bi bi-hospital me-1"></i>
            {{ Auth::user()->etablissement?->nom ?? '' }}
        </div>
        @endif
    </div>

    {{-- Bannière tenant courant (pour admin en mode structure) --}}
    @if(Auth::user()->isGlobal() && isset($currentTenant) && $currentTenant)
    <div style="background:rgba(212,160,23,.15);border-bottom:1px solid rgba(212,160,23,.3);padding:.5rem 1rem;">
        <div style="font-size:.65rem;color:#D4A017;font-weight:700;text-transform:uppercase;letter-spacing:.08em;">{{ __('nav.filtered_view') }}</div>
        <div style="color:#fff;font-size:.78rem;font-weight:600;">{{ $currentTenant->nom }}</div>
        <form method="POST" action="{{ route('superadmin.reset-tenant') }}" class="d-inline">
            @csrf
            <button type="submit" style="background:none;border:none;color:rgba(255,255,255,.6);font-size:.68rem;padding:0;cursor:pointer;text-decoration:underline;">
                {{ __('nav.back_to_global') }}
            </button>
        </form>
    </div>
    @endif

    <!-- Navigation -->
    <div class="py-2">
        @unless(Auth::user()->isClientSignataire())
        <div class="sidebar-section">{{ __('nav.section_navigation') }}</div>
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> {{ __('nav.dashboard') }}
        </a>
        @endunless

        @if(in_array(Auth::user()->role, ['superadmin','admin','qhse','agent','prestataire']))
        <div class="sidebar-section mt-2">{{ __('nav.section_production') }}</div>
        <a href="{{ route('declarations.index') }}" class="sidebar-link {{ request()->is('declarations*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-plus"></i> {{ __('nav.declarations') }}
        </a>
        <a href="{{ route('declarations.create') }}" class="sidebar-link">
            <i class="bi bi-plus-circle"></i> {{ __('nav.new_declaration') }}
        </a>
        <a href="{{ route('stockage.index') }}" class="sidebar-link {{ request()->is('stockage*') ? 'active' : '' }}">
            <i class="bi bi-archive"></i> {{ __('nav.storage') }}
        </a>
        @endif

        @if(in_array(Auth::user()->role, ['superadmin','admin','admin_reseau','qhse','agent','collecteur','prestataire','client_signataire']))
        <div class="sidebar-section mt-2">{{ __('nav.section_transport') }}</div>
        @if(in_array(Auth::user()->role, ['superadmin','admin','qhse','collecteur','prestataire','client_signataire']))
        <a href="{{ route('collectes.index') }}" class="sidebar-link {{ request()->is('collectes*') && ! request()->is('collectes/*/signature') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> {{ __('nav.collectes') }}
        </a>
        @endif
        <a href="{{ route('signatures.index') }}" class="sidebar-link {{ request()->is('signatures*') ? 'active' : '' }}">
            <i class="bi bi-pen"></i> {{ __('nav.signatures') }}
        </a>
        @endif

        @if(in_array(Auth::user()->role, ['superadmin','admin','qhse','prestataire']))
        <div class="sidebar-section mt-2">{{ __('nav.section_destruction') }}</div>
        <a href="{{ route('destructions.index') }}" class="sidebar-link {{ request()->is('destructions*') ? 'active' : '' }}">
            <i class="bi bi-fire"></i> {{ __('nav.destructions') }}
        </a>
        @endif

        @if(in_array(Auth::user()->role, ['superadmin','admin','qhse','prestataire']))
        <div class="sidebar-section mt-2">{{ __('nav.section_compliance') }}</div>
        <a href="{{ route('checklists.index') }}" class="sidebar-link {{ request()->is('checklists*') ? 'active' : '' }}">
            <i class="bi bi-check2-square"></i> {{ __('nav.checklists') }}
        </a>
        <a href="{{ route('rapports.index') }}" class="sidebar-link {{ request()->is('rapports*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph"></i> {{ __('nav.reports') }}
        </a>
        @if(in_array(Auth::user()->role, ['superadmin','admin_reseau','qhse','prestataire']))
        <a href="{{ route('rapports.financier') }}" class="sidebar-link {{ request()->is('rapports/analyse-financiere*') ? 'active' : '' }}">
            <i class="bi bi-currency-dollar"></i> {{ __('nav.financial_analysis') }}
        </a>
        @endif
        @endif

        @unless(Auth::user()->isClientSignataire())
        <div class="sidebar-section mt-2">{{ __('nav.section_alerts') }}</div>
        <a href="{{ route('alertes.index') }}" class="sidebar-link {{ request()->is('alertes*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i> {{ __('nav.alerts') }}
            @php
                $authUser = Auth::user();
                if ($authUser->isGlobal()) {
                    // Si un tenant est sélectionné en session, filtrer par ce tenant
                    if (isset($currentTenant) && $currentTenant) {
                        $alertCount = DB::table('alertes')
                            ->where('etablissement_id', $currentTenant->id)
                            ->where('lu', 0)->count();
                    } else {
                        // Vue globale : toutes les alertes non lues réseau
                        $alertCount = DB::table('alertes')->where('lu', 0)->count();
                    }
                } else {
                    $alertCount = DB::table('alertes')
                        ->where('etablissement_id', $authUser->etablissement_id)
                        ->where('lu', 0)->count();
                }
            @endphp
            @if($alertCount > 0)
            <span class="badge-sidebar">{{ $alertCount }}</span>
            @endif
        </a>
        @endunless

        @if(in_array(Auth::user()->role, ['superadmin','admin','admin_reseau','collecteur','prestataire']))
        <div class="sidebar-section mt-2">
            @if(Auth::user()->isSuperAdmin()) {{ __('nav.section_network_global') }}
            @elseif(Auth::user()->isAdminReseau()) {{ __('nav.section_network_my') }}
            @else {{ __('nav.section_network_view') }}
            @endif
        </div>
        <a href="{{ route('superadmin.index') }}" class="sidebar-link {{ request()->is('superadmin') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i>
            {{ __('nav.network_dashboard') }}
        </a>
        <a href="{{ route('superadmin.etablissements') }}" class="sidebar-link {{ request()->is('superadmin/etablissements*') ? 'active' : '' }}">
            <i class="bi bi-buildings"></i>
            @if(Auth::user()->isAdminReseau()) {{ __('nav.my_establishments') }} @else {{ __('nav.all_structures') }} @endif
        </a>
        <a href="{{ route('superadmin.comparatif') }}" class="sidebar-link {{ request()->is('superadmin/comparatif*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> {{ __('nav.comparative_analysis') }}
        </a>
        @endif

        {{-- Gestion des Réseaux : superadmin uniquement --}}
        @if(Auth::user()->isSuperAdmin())
        <div class="sidebar-section mt-2">{{ __('nav.section_platform') }}</div>
        <a href="{{ route('reseaux.index') }}" class="sidebar-link {{ request()->is('reseaux*') ? 'active' : '' }}">
            <i class="bi bi-share"></i> {{ __('nav.networks') }}
        </a>
        @endif

        @if(in_array(Auth::user()->role, ['superadmin','admin','admin_reseau']))
        <div class="sidebar-section mt-2">{{ __('nav.section_administration') }}</div>
        <a href="{{ route('admin.activites') }}" class="sidebar-link {{ request()->is('admin/activites*') ? 'active' : '' }}">
            <i class="bi bi-activity"></i> {{ __('nav.realtime_activity') }}
            <span class="pulse-dot ms-auto" style="width:8px;height:8px;border-radius:50%;background:#16a34a;animation:pulse 1.5s infinite;flex-shrink:0;"></span>
        </a>
        <a href="{{ route('admin.index') }}" class="sidebar-link {{ request()->is('admin/etablissements*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> {{ __('nav.establishments') }}
        </a>
        <a href="{{ route('admin.utilisateurs.index') }}" class="sidebar-link {{ request()->is('admin/utilisateurs*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> {{ __('nav.users') }}
        </a>
        <a href="{{ route('admin.services') }}" class="sidebar-link {{ request()->is('admin/services*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i> {{ __('nav.services') }}
        </a>
        <a href="{{ route('admin.contenants') }}" class="sidebar-link {{ request()->is('admin/contenants*') ? 'active' : '' }}">
            <i class="bi bi-box"></i> {{ __('nav.containers') }}
            @if(Auth::user()->isAdminReseau())<small class="ms-1 text-muted">{{ __('nav.containers_readonly') }}</small>@endif
        </a>
        @endif
    </div>

    <!-- Profil utilisateur -->
    <div class="mt-auto p-3 border-top border-secondary" style="border-color:rgba(255,255,255,.1)!important;">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar" style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--primary-lt),var(--secondary));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.8rem;flex-shrink:0;">
                {{ strtoupper(substr(Auth::user()->prenom,0,1).substr(Auth::user()->nom,0,1)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="color:#fff;font-size:.82rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ Auth::user()->prenom }} {{ Auth::user()->nom }}
                </div>
                <div><span class="role-badge role-{{ Auth::user()->role }}">{{ Auth::user()->role }}</span></div>
            </div>
        </div>
        <a href="{{ route('account.data') }}" class="btn btn-sm w-100 mt-2" style="background:rgba(255,255,255,.05);color:rgba(255,255,255,.65);border:1px solid rgba(255,255,255,.1);">
            <i class="bi bi-person-vcard me-1"></i> {{ __('account.nav_link') }}
        </a>
        <form action="{{ route('logout') }}" method="POST" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-sm w-100" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);border:1px solid rgba(255,255,255,.1);">
                <i class="bi bi-box-arrow-left me-1"></i> {{ __('nav.logout') }}
            </button>
        </form>
    </div>
</nav>

<!-- ═══ MAIN ═══ -->
<main id="main">
    <!-- TOP BAR -->
    <div class="topbar">
        <button class="btn btn-sm d-md-none me-1" onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div class="topbar-title">@yield('title', __('nav.topbar_default_title'))</div>

        <!-- Alertes rapides -->
        @if(isset($alertCount) && $alertCount > 0)
        <a href="{{ route('alertes.index') }}" class="btn btn-sm btn-outline-danger position-relative">
            <i class="bi bi-bell-fill"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">{{ $alertCount }}</span>
        </a>
        @endif

        {{-- ── Sélecteur de langue (Phase 0 i18n) ──────────────── --}}
        <div class="dropdown me-2">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false"
                    title="{{ __('common.locale_fr') }} / {{ __('common.locale_en') }}">
                <i class="bi bi-translate"></i> {{ strtoupper(app()->getLocale()) }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @foreach(config('app.supported_locales', ['fr','en']) as $loc)
                <li>
                    <form method="POST" action="{{ route('locale.switch', $loc) }}" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item {{ app()->getLocale() === $loc ? 'active fw-bold' : '' }}">
                            {{ __('common.locale_' . $loc) }}
                            @if(app()->getLocale() === $loc)<i class="bi bi-check2 ms-2"></i>@endif
                        </button>
                    </form>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="avatar">{{ strtoupper(substr(Auth::user()->prenom,0,1).substr(Auth::user()->nom,0,1)) }}</div>
            <div class="d-none d-md-block">
                <div style="font-size:.82rem;font-weight:600;color:var(--dark);">{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</div>
                <span class="role-badge role-{{ Auth::user()->role }}">{{ Auth::user()->role }}</span>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>{{ __('common.errors_label') }}</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @include('partials.onboarding')

    @yield('content')

    <!-- FOOTER LÉGAL -->
    <footer class="mt-5 pt-3 pb-4 text-center" style="border-top:1px solid #e5e9ef;font-size:.8rem;color:#6b7280;">
        <span>© {{ date('Y') }} LaBioTrack — {{ __('legal.footer_rights') }}</span>
        <span class="mx-2">·</span>
        <a href="{{ route('legal.mentions') }}" class="text-decoration-none" style="color:#1B6B3A;">{{ __('legal.nav_mentions') }}</a>
        <span class="mx-2">·</span>
        <a href="{{ route('legal.privacy') }}" class="text-decoration-none" style="color:#1B6B3A;">{{ __('legal.nav_privacy') }}</a>
        <span class="mx-2">·</span>
        <a href="{{ route('legal.cgu') }}" class="text-decoration-none" style="color:#1B6B3A;">{{ __('legal.nav_cgu') }}</a>
    </footer>
</main>

<!-- NOTICE COOKIES (cookies strictement nécessaires — informative, dismissible) -->
<div id="cookie-notice" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:1080;background:#1A2332;color:#e5e9ef;padding:.9rem 1.2rem;box-shadow:0 -4px 20px rgba(0,0,0,.2);">
    <div class="d-flex flex-wrap align-items-center justify-content-center gap-2" style="font-size:.85rem;">
        <span><i class="bi bi-shield-check me-1" style="color:#2E8B57;"></i>{{ __('legal.cookie_notice') }}</span>
        <a href="{{ route('legal.privacy') }}" class="text-decoration-none" style="color:#D4A017;">{{ __('legal.cookie_more') }}</a>
        <button type="button" id="cookie-ok" class="btn btn-sm btn-success py-0 px-3">{{ __('legal.cookie_ok') }}</button>
    </div>
</div>

<script>
    (function () {
        try {
            var k = 'lbt_cookie_ack';
            var el = document.getElementById('cookie-notice');
            if (!localStorage.getItem(k)) { el.style.display = 'block'; }
            document.getElementById('cookie-ok').addEventListener('click', function () {
                localStorage.setItem(k, '1');
                el.style.display = 'none';
            });
        } catch (e) { /* localStorage indisponible → on n'affiche rien */ }
    })();

    // État de chargement standardisé : au submit d'un formulaire (non annulé),
    // désactive le bouton et affiche un spinner pour éviter les double-clics.
    (function () {
        // Phase bubbling (sans capture) → s'exécute APRÈS le handler de
        // validation du formulaire ; on ignore si celui-ci a annulé le submit.
        document.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;
            var btn = e.target.querySelector('button[type="submit"], button:not([type])');
            if (!btn || btn.dataset.noSpinner !== undefined) return;
            btn.disabled = true;
            btn.innerHTML = '<span class="lbt-spinner me-2"></span>' + btn.textContent.trim();
        });
    })();
</script>
@stack('scripts')
</body>
</html>
