@extends('layouts.app')
@section('title', 'Dashboard Réseau Global')
@section('content')

{{-- Bannière vue globale --}}
@if($isGlobalView ?? false)
<div class="alert alert-info d-flex align-items-center gap-2 mb-3" style="border-left:4px solid #2980B9;">
    <i class="bi bi-globe fs-5"></i>
    <div class="flex-fill">
        <strong>Vue Réseau Globale</strong> — Vous consultez les données de toutes les structures.
        Cliquez sur une structure pour zoomer.
    </div>
</div>
@endif

{{-- Switch tenant --}}
@if(in_array(Auth::user()->role, ['superadmin','admin']))
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <span class="text-muted fw-semibold" style="font-size:.85rem;"><i class="bi bi-buildings me-1"></i>Basculer vers :</span>
    @foreach($etablissements as $etab)
    <form method="POST" action="{{ route('superadmin.switch-tenant', $etab->id) }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm {{ ($currentTenant?->id == $etab->id) ? 'btn-success' : 'btn-outline-secondary' }}" style="font-size:.78rem;">
            {{ $etab->nom }}
        </button>
    </form>
    @endforeach
    @if($currentTenant)
    <form method="POST" action="{{ route('superadmin.reset-tenant') }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:.78rem;">
            <i class="bi bi-globe me-1"></i>Vue globale
        </button>
    </form>
    @endif
</div>
@endif

<div class="page-header">
    <h4 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2 text-success"></i>Dashboard Réseau — Toutes structures</h4>
    <small class="text-muted">{{ now()->format('l d F Y') }}</small>
</div>

{{-- KPIs Réseau --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:#d1fae5;color:#065f46;"><i class="bi bi-hospital"></i></div>
            <div class="value">{{ $stats['structures_actives'] }}</div>
            <div class="label">Structures actives</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:#dbeafe;color:#1e40af;"><i class="bi bi-people"></i></div>
            <div class="value">{{ $stats['total_users'] }}</div>
            <div class="label">Utilisateurs actifs</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:#fef9c3;color:#92400e;"><i class="bi bi-clipboard-plus"></i></div>
            <div class="value">{{ number_format($stats['declarations_mois']) }}</div>
            <div class="label">Déclarations (mois)</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:#f0fdf4;color:#166534;"><i class="bi bi-weight"></i></div>
            <div class="value">{{ number_format($stats['poids_mois_kg'],1) }}<small class="fs-6"> kg</small></div>
            <div class="label">Poids total (mois)</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:#f0fdf4;color:#166534;"><i class="bi bi-shield-check"></i></div>
            <div class="value {{ $stats['score_moyen'] >= 80 ? 'text-success' : ($stats['score_moyen'] >= 60 ? 'text-warning' : 'text-danger') }}">
                {{ number_format($stats['score_moyen'],0) }}%
            </div>
            <div class="label">Score conformité moyen</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:#fee2e2;color:#991b1b;"><i class="bi bi-bell-fill"></i></div>
            <div class="value text-danger">{{ $stats['alertes_nonlues'] }}</div>
            <div class="label">Alertes non lues</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Graphique évolution réseau --}}
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-graph-up me-2 text-success"></i>Production réseau — 6 mois (kg)</div>
            <div class="card-body"><canvas id="chartEvolutionReseau" height="120"></canvas></div>
        </div>
    </div>
    {{-- Alertes réseau --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bell text-danger me-2"></i>Alertes réseau</span>
                @if($stats['alertes_nonlues'] > 0)
                <span class="badge bg-danger">{{ $stats['alertes_nonlues'] }}</span>
                @endif
            </div>
            <div class="card-body p-0" style="max-height:280px;overflow-y:auto;">
                @forelse($alertesReseau as $a)
                <div class="p-2 border-bottom" style="font-size:.8rem;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-{{ $a->niveau === 'danger' ? 'exclamation-circle-fill text-danger' : 'exclamation-triangle-fill text-warning' }} mt-1"></i>
                        <div>
                            <div class="fw-semibold" style="font-size:.75rem;color:#1B6B3A;">{{ $a->etab_nom }}</div>
                            <div>{{ \Illuminate\Support\Str::limit($a->message, 70) }}</div>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center p-4 text-muted"><i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>Aucune alerte</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Tableau comparatif structures --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2 text-success"></i>Performance par structure — {{ now()->format('F Y') }}</span>
        <a href="{{ route('superadmin.comparatif') }}" class="btn btn-sm btn-outline-success">Analyse comparative</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Structure</th>
                    <th>Type</th>
                    <th class="text-end">Déclarations</th>
                    <th class="text-end">Poids (kg)</th>
                    <th>Conformité</th>
                    <th class="text-end">Alertes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parStructure as $e)
                <tr>
                    <td class="fw-semibold">{{ $e->nom }}</td>
                    <td><span class="badge bg-light text-dark border">{{ ucfirst($e->type) }}</span></td>
                    <td class="text-end">{{ number_format($e->declarations_mois) }}</td>
                    <td class="text-end">{{ number_format($e->poids_mois,1) }}</td>
                    <td style="min-width:140px;">
                        @php $sc = round($e->score_conformite); @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-fill" style="height:8px;">
                                <div class="progress-bar {{ $sc>=80?'bg-success':($sc>=60?'bg-warning':'bg-danger') }}" style="width:{{ $sc }}%"></div>
                            </div>
                            <span style="font-size:.78rem;min-width:32px;">{{ $sc }}%</span>
                        </div>
                    </td>
                    <td class="text-end">
                        @if($e->alertes_nonlues > 0)
                        <span class="badge bg-danger">{{ $e->alertes_nonlues }}</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('superadmin.etablissement', $e->id) }}" class="btn btn-xs btn-sm btn-outline-primary" style="font-size:.72rem;padding:.15rem .4rem;">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('superadmin.switch-tenant', $e->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" style="font-size:.72rem;padding:.15rem .4rem;" title="Basculer vers cette structure">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


{{-- Section spécifique Collecteur --}}
@if(Auth::user()->isCollecteur() && isset($collectesRecentes) && $collectesRecentes->count() > 0)
<div class="card mt-3">
    <div class="card-header"><i class="bi bi-truck me-2 text-success"></i>Mes collectes récentes</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>N° Bordereau</th><th>Établissement</th><th>Contenants</th><th>Poids</th><th>Statut</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($collectesRecentes as $c)
                <tr>
                    <td><code>{{ $c->numero_bordereau ?? '—' }}</code></td>
                    <td>{{ $c->etablissement_nom ?? '—' }}</td>
                    <td>{{ $c->nombre_contenants }}</td>
                    <td>{{ number_format($c->poids_declare_kg ?? 0, 1) }} kg</td>
                    <td><span class="badge bg-{{ ['planifie'=>'secondary','en_cours'=>'warning','complete'=>'success','annule'=>'danger'][$c->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$c->statut)) }}</span></td>
                    <td><small>{{ \Carbon\Carbon::parse($c->date_collecte)->format('d/m/Y') }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-2 text-end"><a href="{{ route('collectes.index') }}" class="btn btn-sm btn-outline-success">Voir toutes mes collectes</a></div>
</div>
@endif

{{-- Section spécifique Prestataire --}}
@if(Auth::user()->isPrestataire() && isset($destructionsRecentes) && $destructionsRecentes->count() > 0)
<div class="card mt-3">
    <div class="card-header"><i class="bi bi-fire me-2 text-danger"></i>Mes destructions récentes</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Certificat</th><th>Établissement</th><th>Poids réel</th><th>Méthode</th><th>Conforme</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($destructionsRecentes as $d)
                <tr>
                    <td><code>{{ $d->certificat_numero ?? '—' }}</code></td>
                    <td>{{ $d->etablissement_nom ?? '—' }}</td>
                    <td>{{ number_format($d->poids_reel_kg, 1) }} kg</td>
                    <td>{{ ucfirst(str_replace('_',' ',$d->methode)) }}</td>
                    <td><span class="badge bg-{{ $d->conforme ? 'success' : 'danger' }}">{{ $d->conforme ? 'Conforme' : 'Non conforme' }}</span></td>
                    <td><small>{{ \Carbon\Carbon::parse($d->date_destruction)->format('d/m/Y') }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-2 text-end"><a href="{{ route('destructions.index') }}" class="btn btn-sm btn-outline-danger">Voir toutes mes destructions</a></div>
</div>
@endif

@endsection
@push('scripts')
<script>
const evo = @json($evolution);
new Chart(document.getElementById('chartEvolutionReseau'), {
    type:'line',
    data:{
        labels: evo.map(e=>e.mois),
        datasets:[{
            label:'Poids réseau (kg)',
            data: evo.map(e=>e.poids),
            borderColor:'#1B6B3A',
            backgroundColor:'rgba(27,107,58,.08)',
            borderWidth:2.5,fill:true,tension:.4,
            pointBackgroundColor:'#1B6B3A',pointRadius:4,
        },{
            label:'Nombre de déclarations',
            data: evo.map(e=>e.count),
            borderColor:'#D4A017',
            backgroundColor:'rgba(212,160,23,.06)',
            borderWidth:2,fill:false,tension:.4,
            yAxisID:'y2',pointRadius:3,
        }]
    },
    options:{
        responsive:true,
        plugins:{legend:{position:'bottom'}},
        scales:{
            y:{beginAtZero:true,grid:{color:'#f0f0f0'},title:{display:true,text:'kg'}},
            y2:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},title:{display:true,text:'déclarations'}},
            x:{grid:{display:false}}
        }
    }
});
</script>
@endpush
