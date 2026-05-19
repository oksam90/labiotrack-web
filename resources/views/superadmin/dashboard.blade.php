@extends('layouts.app')
@section('title', __('superadmin.page_dashboard_title'))
@section('content')

{{-- Bannière vue globale --}}
@if($isGlobalView ?? false)
<div class="alert alert-info d-flex align-items-center gap-2 mb-3" style="border-left:4px solid #2980B9;">
    <i class="bi bi-globe fs-5"></i>
    <div class="flex-fill">
        <strong>{{ __('superadmin.banner_global_title') }}</strong> — {{ __('superadmin.banner_global_desc') }}
    </div>
</div>
@endif

{{-- Switch tenant --}}
@if(in_array(Auth::user()->role, ['superadmin','admin']))
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <span class="text-muted fw-semibold" style="font-size:.85rem;"><i class="bi bi-buildings me-1"></i>{{ __('superadmin.switch_to') }}</span>
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
            <i class="bi bi-globe me-1"></i>{{ __('superadmin.btn_global_view') }}
        </button>
    </form>
    @endif
</div>
@endif

<div class="page-header">
    <h4 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2 text-success"></i>{{ __('superadmin.header_dashboard') }}</h4>
    <small class="text-muted">{{ now()->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY') }}</small>
</div>

{{-- KPIs Réseau --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:#d1fae5;color:#065f46;"><i class="bi bi-hospital"></i></div>
            <div class="value">{{ $stats['structures_actives'] }}</div>
            <div class="label">{{ __('superadmin.kpi_active_structures') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:#dbeafe;color:#1e40af;"><i class="bi bi-people"></i></div>
            <div class="value">{{ $stats['total_users'] }}</div>
            <div class="label">{{ __('superadmin.kpi_active_users') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:#fef9c3;color:#92400e;"><i class="bi bi-clipboard-plus"></i></div>
            <div class="value">{{ number_format($stats['declarations_mois']) }}</div>
            <div class="label">{{ __('superadmin.kpi_decl_month') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:#f0fdf4;color:#166534;"><i class="bi bi-weight"></i></div>
            <div class="value">{{ number_format($stats['poids_mois_kg'],1) }}<small class="fs-6"> kg</small></div>
            <div class="label">{{ __('superadmin.kpi_total_weight') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:#f0fdf4;color:#166534;"><i class="bi bi-shield-check"></i></div>
            <div class="value {{ $stats['score_moyen'] >= 80 ? 'text-success' : ($stats['score_moyen'] >= 60 ? 'text-warning' : 'text-danger') }}">
                {{ number_format($stats['score_moyen'],0) }}%
            </div>
            <div class="label">{{ __('superadmin.kpi_avg_compliance') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:#fee2e2;color:#991b1b;"><i class="bi bi-bell-fill"></i></div>
            <div class="value text-danger">{{ $stats['alertes_nonlues'] }}</div>
            <div class="label">{{ __('superadmin.kpi_unread_alerts') }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Graphique évolution réseau --}}
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-graph-up me-2 text-success"></i>{{ __('superadmin.card_network_evolution') }}</div>
            <div class="card-body"><canvas id="chartEvolutionReseau" height="120"
                data-label-weight="{{ __('superadmin.chart_label_network_weight') }}"
                data-label-count="{{ __('superadmin.chart_label_decl_count') }}"
                data-axis-kg="{{ __('superadmin.chart_axis_kg') }}"
                data-axis-decl="{{ __('superadmin.chart_axis_decl') }}"></canvas></div>
        </div>
    </div>
    {{-- Alertes réseau --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bell text-danger me-2"></i>{{ __('superadmin.card_network_alerts') }}</span>
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
                            <small class="text-muted">{{ \Carbon\Carbon::parse($a->created_at)->locale(app()->getLocale())->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center p-4 text-muted"><i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>{{ __('superadmin.no_alerts') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Tableau comparatif structures --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2 text-success"></i>{{ __('superadmin.card_performance', ['month' => now()->locale(app()->getLocale())->translatedFormat('F Y')]) }}</span>
        <a href="{{ route('superadmin.comparatif') }}" class="btn btn-sm btn-outline-success">{{ __('superadmin.btn_comparative') }}</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('superadmin.col_struct') }}</th>
                    <th>{{ __('superadmin.col_type') }}</th>
                    <th class="text-end">{{ __('superadmin.col_declarations') }}</th>
                    <th class="text-end">{{ __('superadmin.col_weight') }}</th>
                    <th>{{ __('superadmin.col_compliance') }}</th>
                    <th class="text-end">{{ __('superadmin.col_alerts') }}</th>
                    <th>{{ __('superadmin.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parStructure as $e)
                <tr>
                    <td class="fw-semibold">{{ $e->nom }}</td>
                    <td><span class="badge bg-light text-dark border">{{ __('admin.etab_type_' . $e->type) }}</span></td>
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
                                <button type="submit" class="btn btn-sm btn-outline-success" style="font-size:.72rem;padding:.15rem .4rem;" title="{{ __('superadmin.btn_switch_to_struct') }}">
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
    <div class="card-header"><i class="bi bi-truck me-2 text-success"></i>{{ __('superadmin.card_my_collectes') }}</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr>
                <th>{{ __('superadmin.col_bordereau') }}</th>
                <th>{{ __('superadmin.col_etab') }}</th>
                <th>{{ __('superadmin.col_containers') }}</th>
                <th>{{ __('superadmin.col_weight_short') }}</th>
                <th>{{ __('superadmin.col_status') ?? __('common.status') }}</th>
                <th>{{ __('superadmin.col_date') }}</th>
            </tr></thead>
            <tbody>
                @foreach($collectesRecentes as $c)
                <tr>
                    <td><code>{{ $c->numero_bordereau ?? '—' }}</code></td>
                    <td>{{ $c->etablissement_nom ?? '—' }}</td>
                    <td>{{ $c->nombre_contenants }}</td>
                    <td>{{ number_format($c->poids_declare_kg ?? 0, 1) }} kg</td>
                    <td><span class="badge bg-{{ ['planifie'=>'secondary','en_cours'=>'warning','signee'=>'success','complete'=>'success','annule'=>'danger'][$c->statut] ?? 'secondary' }}">{{ __('collectes.status_' . $c->statut) }}</span></td>
                    <td><small>{{ \Carbon\Carbon::parse($c->date_collecte)->format('d/m/Y') }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-2 text-end"><a href="{{ route('collectes.index') }}" class="btn btn-sm btn-outline-success">{{ __('superadmin.btn_view_all_my_collectes') }}</a></div>
</div>
@endif

{{-- Section spécifique Prestataire --}}
@if(Auth::user()->isPrestataire() && isset($destructionsRecentes) && $destructionsRecentes->count() > 0)
<div class="card mt-3">
    <div class="card-header"><i class="bi bi-fire me-2 text-danger"></i>{{ __('superadmin.card_my_destructions') }}</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr>
                <th>{{ __('superadmin.col_certificat') }}</th>
                <th>{{ __('superadmin.col_etab') }}</th>
                <th>{{ __('superadmin.col_weight_real') }}</th>
                <th>{{ __('superadmin.col_method') }}</th>
                <th>{{ __('superadmin.col_conform') }}</th>
                <th>{{ __('superadmin.col_date') }}</th>
            </tr></thead>
            <tbody>
                @foreach($destructionsRecentes as $d)
                <tr>
                    <td><code>{{ $d->certificat_numero ?? '—' }}</code></td>
                    <td>{{ $d->etablissement_nom ?? '—' }}</td>
                    <td>{{ number_format($d->poids_reel_kg, 1) }} kg</td>
                    @php $methodKey = match($d->methode){'incineration'=>'incineration','autoclave'=>'autoclave','desinfection_chimique'=>'desinfection','autre'=>'other',default=>'other'}; @endphp
                    <td>{{ __('destructions.method_' . $methodKey) }}</td>
                    <td><span class="badge bg-{{ $d->conforme ? 'success' : 'danger' }}">{{ $d->conforme ? __('superadmin.badge_conform') : __('superadmin.badge_nonconform') }}</span></td>
                    <td><small>{{ \Carbon\Carbon::parse($d->date_destruction)->format('d/m/Y') }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-2 text-end"><a href="{{ route('destructions.index') }}" class="btn btn-sm btn-outline-danger">{{ __('superadmin.btn_view_all_my_destructions') }}</a></div>
</div>
@endif

@endsection
@push('scripts')
<script>
const evo = @json($evolution);
const chartCanvas = document.getElementById('chartEvolutionReseau');
new Chart(chartCanvas, {
    type:'line',
    data:{
        labels: evo.map(e=>e.mois),
        datasets:[{
            label: chartCanvas.dataset.labelWeight,
            data: evo.map(e=>e.poids),
            borderColor:'#1B6B3A',
            backgroundColor:'rgba(27,107,58,.08)',
            borderWidth:2.5,fill:true,tension:.4,
            pointBackgroundColor:'#1B6B3A',pointRadius:4,
        },{
            label: chartCanvas.dataset.labelCount,
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
            y:{beginAtZero:true,grid:{color:'#f0f0f0'},title:{display:true,text:chartCanvas.dataset.axisKg}},
            y2:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},title:{display:true,text:chartCanvas.dataset.axisDecl}},
            x:{grid:{display:false}}
        }
    }
});
</script>
@endpush
