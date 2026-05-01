<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Déchets Biomédicaux') — Plateforme LaBioTrack</title>

    <!-- Bootstrap 5 + Icons -->
    <link rel="icon" type="image/png" href="{{ asset('labiotrack-favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --primary:    #1B6B3A;
            --primary-lt: #2E8B57;
            --secondary:  #D4A017;
            --danger:     #C0392B;
            --warning:    #E67E22;
            --info:       #2980B9;
            --dark:       #1A2332;
            --sidebar-w:  260px;
        }
        body { background:#F4F7F6; font-family:'Segoe UI',system-ui,sans-serif; }

        /* ── SIDEBAR ── */
        #sidebar {
            position: fixed; top:0; left:0; height:100vh; width:var(--sidebar-w);
            background: var(--dark);
            overflow-y:auto; z-index:1000; transition:.3s;
        }
        .sidebar-brand {
            padding:1.2rem 1.5rem;
            background: linear-gradient(135deg,var(--primary),#0d4024);
            color:#fff;
        }
        .sidebar-brand h6 { font-size:.65rem; opacity:.7; letter-spacing:.1em; text-transform:uppercase; }
        .sidebar-brand h4 { font-size:1.1rem; font-weight:700; margin:0; }
        .sidebar-brand .bi { font-size:1.6rem; }

        .sidebar-section {
            padding:.5rem 1rem .2rem;
            font-size:.65rem; letter-spacing:.12em; text-transform:uppercase;
            color:rgba(255,255,255,.35); font-weight:600;
        }
        .sidebar-link {
            display:flex; align-items:center; gap:.7rem;
            padding:.6rem 1.4rem; color:rgba(255,255,255,.75);
            text-decoration:none; font-size:.88rem; transition:.15s;
            border-left:3px solid transparent;
        }
        .sidebar-link:hover, .sidebar-link.active {
            color:#fff; background:rgba(255,255,255,.07);
            border-left-color: var(--secondary);
        }
        .sidebar-link .bi { font-size:1rem; width:20px; }
        .badge-sidebar {
            margin-left:auto; font-size:.65rem;
            background:var(--danger); color:#fff; border-radius:10px; padding:.15rem .5rem;
        }

        /* ── MAIN CONTENT ── */
        #main { margin-left:var(--sidebar-w); padding:1.5rem; }

        /* ── TOP NAV ── */
        .topbar {
            background:#fff; border-bottom:1px solid #e5e9ef;
            padding:.75rem 1.5rem; display:flex; align-items:center; gap:1rem;
            margin:-1.5rem -1.5rem 1.5rem; position:sticky; top:0; z-index:100;
        }
        .topbar-title { font-weight:600; font-size:1.1rem; color:var(--dark); flex:1; }
        .topbar .avatar {
            width:36px; height:36px; border-radius:50%;
            background: linear-gradient(135deg,var(--primary-lt),var(--secondary));
            display:flex; align-items:center; justify-content:center;
            color:#fff; font-weight:700; font-size:.85rem;
        }
        .role-badge {
            font-size:.68rem; padding:.2rem .6rem; border-radius:20px; font-weight:600;
            text-transform:uppercase; letter-spacing:.05em;
        }
        .role-superadmin { background:#2c3e50; color:#fff; }
        .role-admin      { background:#8e44ad; color:#fff; }
        .role-qhse       { background:var(--primary); color:#fff; }
        .role-agent      { background:var(--info); color:#fff; }
        .role-collecteur { background:var(--warning); color:#fff; }
        .role-prestataire{ background:#7f8c8d; color:#fff; }

        /* ── CARDS ── */
        .stat-card {
            background:#fff; border-radius:12px; padding:1.3rem 1.5rem;
            border:1px solid #e5e9ef; position:relative; overflow:hidden;
        }
        .stat-card .icon {
            width:52px; height:52px; border-radius:12px;
            display:flex; align-items:center; justify-content:center;
            font-size:1.4rem;
        }
        .stat-card .value { font-size:1.8rem; font-weight:700; color:var(--dark); }
        .stat-card .label { font-size:.82rem; color:#6b7280; margin-top:.1rem; }
        .stat-card .trend { font-size:.78rem; font-weight:600; }
        .trend-up   { color:#16a34a; }
        .trend-down { color:#dc2626; }

        /* ── TABLE ── */
        .table { font-size:.88rem; }
        .table thead th { background:#F4F7F6; font-weight:600; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; border-bottom:2px solid #e5e9ef; }

        /* ── STATUT BADGES ── */
        .statut-en_stock     { background:#d1fae5; color:#065f46; }
        .statut-en_transport { background:#dbeafe; color:#1e40af; }
        .statut-detruit      { background:#f3f4f6; color:#374151; }
        .statut-annule       { background:#fee2e2; color:#991b1b; }
        .statut-badge { font-size:.72rem; padding:.25rem .6rem; border-radius:20px; font-weight:600; }

        /* ── FORMS ── */
        .form-label { font-weight:600; font-size:.85rem; color:#374151; }
        .form-control, .form-select { border:1.5px solid #e5e9ef; border-radius:8px; font-size:.9rem; }
        .form-control:focus, .form-select:focus { border-color:var(--primary-lt); box-shadow:0 0 0 3px rgba(46,139,87,.12); }

        /* ── ALERTS ── */
        .alert-info    { background:#eff6ff; border-color:#bfdbfe; color:#1d4ed8; }
        .alert-success { background:#f0fdf4; border-color:#bbf7d0; color:#166534; }
        .alert-warning { background:#fffbeb; border-color:#fde68a; color:#92400e; }
        .alert-danger  { background:#fef2f2; border-color:#fecaca; color:#991b1b; }

        /* ── MOBILE ── */
        @media(max-width:768px){
            #sidebar { transform:translateX(-100%); }
            #sidebar.show { transform:translateX(0); }
            #main { margin-left:0; padding:1rem; }
        }

        .page-header { border-bottom:2px solid #e5e9ef; padding-bottom:1rem; margin-bottom:1.5rem; }
        @keyframes pulse {
            0%,100% { opacity:1; transform:scale(1); }
            50%      { opacity:.5; transform:scale(1.3); }
        }
        .btn-primary { background:var(--primary); border-color:var(--primary); }
        .btn-primary:hover { background:var(--primary-lt); border-color:var(--primary-lt); }
        .card { border:1px solid #e5e9ef; border-radius:12px; }
        .card-header { background:#F4F7F6; border-bottom:1px solid #e5e9ef; font-weight:600; }

        /* ── PROGRESS CONFORMITÉ ── */
        .score-ring { position:relative; display:inline-flex; align-items:center; justify-content:center; }
        .score-ring svg { transform:rotate(-90deg); }
        .score-ring .score-text { position:absolute; font-weight:700; font-size:1.1rem; color:var(--dark); }
    </style>
    @stack('styles')
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-virus2 text-success"></i>
            <div>
                <h6 class="mb-0">Plateforme</h6>
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
        <div style="font-size:.65rem;color:#D4A017;font-weight:700;text-transform:uppercase;letter-spacing:.08em;">Vue filtrée</div>
        <div style="color:#fff;font-size:.78rem;font-weight:600;">{{ $currentTenant->nom }}</div>
        <form method="POST" action="{{ route('superadmin.reset-tenant') }}" class="d-inline">
            @csrf
            <button type="submit" style="background:none;border:none;color:rgba(255,255,255,.6);font-size:.68rem;padding:0;cursor:pointer;text-decoration:underline;">
                ← Vue réseau globale
            </button>
        </form>
    </div>
    @endif

    <!-- Navigation -->
    <div class="py-2">
        <div class="sidebar-section">Navigation</div>
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Tableau de bord
        </a>

        @if(in_array(Auth::user()->role, ['superadmin','admin','qhse','agent','prestataire']))
        <div class="sidebar-section mt-2">Production</div>
        <a href="{{ route('declarations.index') }}" class="sidebar-link {{ request()->is('declarations*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-plus"></i> Déclarations
        </a>
        <a href="{{ route('declarations.create') }}" class="sidebar-link">
            <i class="bi bi-plus-circle"></i> Nouvelle déclaration
        </a>
        <a href="{{ route('stockage.index') }}" class="sidebar-link {{ request()->is('stockage*') ? 'active' : '' }}">
            <i class="bi bi-archive"></i> Stockage interne
        </a>
        @endif

        @if(in_array(Auth::user()->role, ['superadmin','admin','admin_reseau','qhse','agent','collecteur','prestataire']))
        <div class="sidebar-section mt-2">Transport</div>
        @if(in_array(Auth::user()->role, ['superadmin','admin','qhse','collecteur','prestataire']))
        <a href="{{ route('collectes.index') }}" class="sidebar-link {{ request()->is('collectes*') && ! request()->is('collectes/*/signature') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> Collectes
        </a>
        @endif
        <a href="{{ route('signatures.index') }}" class="sidebar-link {{ request()->is('signatures*') ? 'active' : '' }}">
            <i class="bi bi-pen"></i> Signatures
        </a>
        @endif

        @if(in_array(Auth::user()->role, ['superadmin','admin','qhse','prestataire']))
        <div class="sidebar-section mt-2">Destruction</div>
        <a href="{{ route('destructions.index') }}" class="sidebar-link {{ request()->is('destructions*') ? 'active' : '' }}">
            <i class="bi bi-fire"></i> Destructions
        </a>
        @endif

        @if(in_array(Auth::user()->role, ['superadmin','admin','qhse','prestataire']))
        <div class="sidebar-section mt-2">Conformité & Analyse</div>
        <a href="{{ route('checklists.index') }}" class="sidebar-link {{ request()->is('checklists*') ? 'active' : '' }}">
            <i class="bi bi-check2-square"></i> Checklists
        </a>
        <a href="{{ route('rapports.index') }}" class="sidebar-link {{ request()->is('rapports*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph"></i> Rapports
        </a>
        @if(in_array(Auth::user()->role, ['superadmin','admin_reseau','qhse','prestataire']))
        <a href="{{ route('rapports.financier') }}" class="sidebar-link {{ request()->is('rapports/analyse-financiere*') ? 'active' : '' }}">
            <i class="bi bi-currency-dollar"></i> Analyse financière
        </a>
        @endif
        @endif

        <div class="sidebar-section mt-2">Alertes</div>
        <a href="{{ route('alertes.index') }}" class="sidebar-link {{ request()->is('alertes*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i> Alertes
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

        @if(in_array(Auth::user()->role, ['superadmin','admin','admin_reseau','collecteur','prestataire']))
        <div class="sidebar-section mt-2">
            @if(Auth::user()->isSuperAdmin()) Réseau Global
            @elseif(Auth::user()->isAdminReseau()) Mon Réseau
            @else Vue Réseau
            @endif
        </div>
        <a href="{{ route('superadmin.index') }}" class="sidebar-link {{ request()->is('superadmin') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i>
            @if(Auth::user()->isAdminReseau()) Dashboard Réseau @else Dashboard Réseau @endif
        </a>
        <a href="{{ route('superadmin.etablissements') }}" class="sidebar-link {{ request()->is('superadmin/etablissements*') ? 'active' : '' }}">
            <i class="bi bi-buildings"></i>
            @if(Auth::user()->isAdminReseau()) Mes établissements @else Toutes les structures @endif
        </a>
        <a href="{{ route('superadmin.comparatif') }}" class="sidebar-link {{ request()->is('superadmin/comparatif*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Analyse comparative
        </a>
        @endif

        {{-- Gestion des Réseaux : superadmin uniquement --}}
        @if(Auth::user()->isSuperAdmin())
        <div class="sidebar-section mt-2">Plateforme</div>
        <a href="{{ route('reseaux.index') }}" class="sidebar-link {{ request()->is('reseaux*') ? 'active' : '' }}">
            <i class="bi bi-share"></i> Réseaux
        </a>
        @endif

        @if(in_array(Auth::user()->role, ['superadmin','admin','admin_reseau']))
        <div class="sidebar-section mt-2">Administration</div>
        <a href="{{ route('admin.activites') }}" class="sidebar-link {{ request()->is('admin/activites*') ? 'active' : '' }}">
            <i class="bi bi-activity"></i> Activités temps réel
            <span class="pulse-dot ms-auto" style="width:8px;height:8px;border-radius:50%;background:#16a34a;animation:pulse 1.5s infinite;flex-shrink:0;"></span>
        </a>
        <a href="{{ route('admin.index') }}" class="sidebar-link {{ request()->is('admin/etablissements*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Établissements
        </a>
        <a href="{{ route('admin.utilisateurs.index') }}" class="sidebar-link {{ request()->is('admin/utilisateurs*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Utilisateurs
        </a>
        <a href="{{ route('admin.services') }}" class="sidebar-link {{ request()->is('admin/services*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i> Services
        </a>
        <a href="{{ route('admin.contenants') }}" class="sidebar-link {{ request()->is('admin/contenants*') ? 'active' : '' }}">
            <i class="bi bi-box"></i> Contenants
            @if(Auth::user()->isAdminReseau())<small class="ms-1 text-muted">(lecture)</small>@endif
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
        <form action="{{ route('logout') }}" method="POST" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-sm w-100" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);border:1px solid rgba(255,255,255,.1);">
                <i class="bi bi-box-arrow-left me-1"></i> Déconnexion
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
        <div class="topbar-title">@yield('title', 'Tableau de bord')</div>

        <!-- Alertes rapides -->
        @if(isset($alertCount) && $alertCount > 0)
        <a href="{{ route('alertes.index') }}" class="btn btn-sm btn-outline-danger position-relative">
            <i class="bi bi-bell-fill"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">{{ $alertCount }}</span>
        </a>
        @endif

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
            <strong>Erreurs :</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
