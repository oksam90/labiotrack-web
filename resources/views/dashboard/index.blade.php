@extends('layouts.app')
@section('title', 'Tableau de bord')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-0" style="color:#1A2332;">Tableau de bord</h4>
        <small class="text-muted">{{ now()->format('l d F Y') }} — {{ $etablissement->nom ?? 'Plateforme' }}</small>
    </div>
   <!--  <div class="d-flex gap-2">
        @can('qhse')
        <a href="{{ route('rapports.generer') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-pdf me-1"></i>Rapport</a>
        @endcan
        <a href="{{ route('declarations.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus me-1"></i>Déclarer</a>
    </div> -->
    <div class="d-flex gap-2">
        @if(in_array(Auth::user()->role, ['qhse','admin','superadmin','prestataire']))
        <a href="{{ route('rapports.index') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-pdf me-1"></i>Rapport</a>
        @endif
        <a href="{{ route('declarations.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus me-1"></i>Déclarer</a>
    </div>
</div>

<!-- ══ KPI CARDS ══ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="label">Déclarations (mois)</div>
                    <div class="value">{{ number_format($declarationsMois) }}</div>
                    <div class="trend {{ $variationPoids >= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="bi bi-arrow-{{ $variationPoids >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($variationPoids) }}% vs mois préc.
                    </div>
                </div>
                <div class="icon" style="background:#d1fae5;color:#065f46;"><i class="bi bi-clipboard-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="label">Poids estimé (kg)</div>
                    <div class="value">{{ number_format($poidsMois, 1) }}</div>
                    <div class="text-muted" style="font-size:.78rem;">{{ $ratioKgLit }} kg/lit</div>
                </div>
                <div class="icon" style="background:#dbeafe;color:#1e40af;"><i class="bi bi-weight"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="label">En stock</div>
                    <div class="value text-warning">{{ $enStock }}</div>
                    <div class="text-muted" style="font-size:.78rem;">{{ $enTransport }} en transit</div>
                </div>
                <div class="icon" style="background:#fef3c7;color:#92400e;"><i class="bi bi-archive"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="label">Conformité</div>
                    <div class="value {{ $scoreConformite >= 80 ? 'text-success' : ($scoreConformite >= 60 ? 'text-warning' : 'text-danger') }}">
                        {{ number_format($scoreConformite, 0) }}%
                    </div>
                    <div class="text-muted" style="font-size:.78rem;">Score checklist</div>
                </div>
                <div class="icon" style="background:#f0fdf4;color:#166534;"><i class="bi bi-shield-check"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- ══ GRAPHIQUES ══ -->
<div class="row g-3 mb-4">
    <!-- Évolution 6 mois -->
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up me-2 text-success"></i>Production — 6 derniers mois (kg)</span>
            </div>
            <div class="card-body">
                <canvas id="chartEvolution" height="120"></canvas>
            </div>
        </div>
    </div>
    <!-- Répartition contenants -->
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2 text-primary"></i>Répartition des contenants
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="chartContenants" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODULE PERTES FINANCIÈRES + SERVICES ══ -->
<div class="row g-3 mb-4">
    <!-- Analyse financière -->
    <div class="col-md-4">
        <div class="card h-100 border-warning">
            <div class="card-header bg-warning bg-opacity-10 border-warning">
                <i class="bi bi-currency-dollar me-2 text-warning"></i><strong>Analyse financière</strong>
            </div>
            <div class="card-body">
                @php
                    $coutTotal = ($sacJaune ?? 0) + ($sacNoir ?? 0);
                    $surcout   = $coutTotal * 0.15; // estimation 15% surcoût mauvais tri
                    $economie  = $surcout;
                @endphp
                <div class="mb-3 p-3 rounded" style="background:#fef3c7;">
                    <div style="font-size:.78rem;color:#92400e;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Coût actuel estimé</div>
                    <div style="font-size:1.4rem;font-weight:700;color:#92400e;">{{ number_format($coutTotal) }} <small style="font-size:.7rem;">FCFA</small></div>
                </div>
                <div class="mb-3 p-3 rounded" style="background:#f0fdf4;">
                    <div style="font-size:.78rem;color:#166534;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Coût optimisé possible</div>
                    <div style="font-size:1.4rem;font-weight:700;color:#166534;">{{ number_format($coutTotal - $surcout) }} <small style="font-size:.7rem;">FCFA</small></div>
                </div>
                <div class="p-3 rounded border-2" style="background:#eff6ff;border:2px solid #bfdbfe;">
                    <div style="font-size:.78rem;color:#1e40af;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Économie potentielle</div>
                    <div style="font-size:1.4rem;font-weight:700;color:#1e40af;">{{ number_format($economie) }} <small style="font-size:.7rem;">FCFA / mois</small></div>
                </div>
                <a href="{{ route('rapports.financier') }}" class="btn btn-sm btn-warning mt-3 w-100">
                    <i class="bi bi-arrow-right me-1"></i>Analyse détaillée
                </a>
            </div>
        </div>
    </div>

    <!-- Production par service -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2 text-primary"></i>Production par service (mois en cours)
            </div>
            <div class="card-body">
                <canvas id="chartServices" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ══ DERNIÈRES ACTIVITÉS + ALERTES ══ -->
<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Dernières déclarations</span>
                <a href="{{ route('declarations.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Service</th><th>Contenant</th><th>Qté</th><th>Poids est.</th><th>Statut</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach($derniereActivites as $d)
                        <tr>
                            <td>{{ $d->service_nom }}</td>
                            <td><small>{{ $d->contenant_nom }}</small></td>
                            <td><strong>{{ $d->nombre_contenants }}</strong></td>
                            <td>{{ number_format($d->poids_estime_kg,1) }} kg</td>
                            <td><span class="statut-badge statut-{{ $d->statut }}">{{ str_replace('_',' ',ucfirst($d->statut)) }}</span></td>
                            <td><small class="text-muted">{{ \Carbon\Carbon::parse($d->date_declaration)->format('d/m') }}</small></td>
                        </tr>
                        @endforeach
                        @if($derniereActivites->isEmpty())
                        <tr><td colspan="6" class="text-center text-muted py-3">Aucune déclaration ce mois</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Alertes récentes -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bell me-2 text-danger"></i>Alertes récentes</span>
                @if($alertesNonLues > 0)
                <span class="badge bg-danger">{{ $alertesNonLues }} non lues</span>
                @endif
            </div>
            <div class="card-body p-0">
                @foreach($alertesRecentes as $alerte)
                <div class="d-flex gap-2 p-3 border-bottom {{ $alerte->lu ? '' : 'bg-light' }}">
                    <div class="flex-shrink-0 mt-1">
                        @if($alerte->niveau === 'danger') <i class="bi bi-exclamation-circle-fill text-danger"></i>
                        @elseif($alerte->niveau === 'warning') <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                        @else <i class="bi bi-info-circle-fill text-info"></i>
                        @endif
                    </div>
                    <div style="min-width:0;">
                        <div style="font-size:.82rem;font-weight:{{ $alerte->lu ? '400' : '600' }};">
                            {{ Str::limit($alerte->message, 80) }}
                        </div>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($alerte->created_at)->diffForHumans() }}</small>
                    </div>
                </div>
                @endforeach
                @if($alertesRecentes->isEmpty())
                <div class="text-center text-muted p-4"><i class="bi bi-check-circle-fill text-success fs-3"></i><p class="mt-2 mb-0">Aucune alerte</p></div>
                @endif
                <div class="p-2 text-center">
                    <a href="{{ route('alertes.index') }}" class="btn btn-sm btn-outline-secondary">Voir toutes les alertes</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Évolution 6 mois
const evo = @json($evolution6mois);
new Chart(document.getElementById('chartEvolution'), {
    type: 'line',
    data: {
        labels: evo.map(e => e.mois),
        datasets: [{
            label: 'Poids (kg)',
            data: evo.map(e => e.poids),
            borderColor: '#1B6B3A',
            backgroundColor: 'rgba(27,107,58,.08)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#1B6B3A',
            pointRadius: 4,
        }]
    },
    options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true, grid:{color:'#f0f0f0'}}, x:{grid:{display:false}}} }
});

// Répartition contenants
const cont = @json($repartitionContenants);
new Chart(document.getElementById('chartContenants'), {
    type: 'doughnut',
    data: {
        labels: cont.map(c => c.nom),
        datasets: [{
            data: cont.map(c => c.total),
            backgroundColor: ['#1B6B3A','#2E8B57','#D4A017','#E67E22','#2980B9','#8e44ad','#7f8c8d'],
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: { responsive:true, plugins:{legend:{position:'bottom', labels:{font:{size:11},boxWidth:12}}} }
});

// Services
const srv = @json($productionParService);
new Chart(document.getElementById('chartServices'), {
    type: 'bar',
    data: {
        labels: srv.map(s => s.nom),
        datasets: [{
            label: 'Poids kg',
            data: srv.map(s => s.poids_total),
            backgroundColor: '#1B6B3A',
            borderRadius: 6,
        }]
    },
    options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true, grid:{color:'#f0f0f0'}}, x:{grid:{display:false}}} }
});
</script>
@endpush
