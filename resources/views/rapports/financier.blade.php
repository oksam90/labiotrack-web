@extends('layouts.app')
@section('title','Analyse Financière')
@section('content')

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-currency-dollar me-2 text-success"></i>Analyse Financière</h4>
        <small class="text-muted">Coûts de gestion des déchets — {{ \Carbon\Carbon::createFromFormat('Y-m', $mois)->translatedFormat('F Y') }}</small>
    </div>
    <a href="{{ route('rapports.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour aux rapports
    </a>
</div>

{{-- KPI CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="icon" style="background:#d1fae5;"><i class="bi bi-cash-stack text-success fs-4"></i></div>
                <div>
                    <div class="value">{{ number_format($coutParContenant->sum('cout_total'), 0, ',', ' ') }} F</div>
                    <div class="label">Coût total du mois</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="icon" style="background:#fef9c3;"><i class="bi bi-exclamation-triangle text-warning fs-4"></i></div>
                <div>
                    <div class="value">{{ number_format($surcoutEstime, 0, ',', ' ') }} F</div>
                    <div class="label">Surcoût estimé (mauvais tri)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="icon" style="background:#dcfce7;"><i class="bi bi-piggy-bank text-success fs-4"></i></div>
                <div>
                    <div class="value">{{ number_format($economie, 0, ',', ' ') }} F</div>
                    <div class="label">Économie potentielle</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="icon" style="background:#ede9fe;"><i class="bi bi-recycle text-purple fs-4"></i></div>
                <div>
                    <div class="value">{{ number_format($coutOptimise, 0, ',', ' ') }} F</div>
                    <div class="label">Coût optimisé estimé</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Coût par type de contenant --}}
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-box me-2 text-success"></i>Coûts par type de contenant
            </div>
            <div class="card-body p-0">
                @if($coutParContenant->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>Aucune déclaration ce mois
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Type de contenant</th>
                                <th class="text-end">Qté</th>
                                <th class="text-end">P.U.</th>
                                <th class="text-end">Coût total</th>
                                <th class="text-end">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = $coutParContenant->sum('cout_total'); @endphp
                            @foreach($coutParContenant as $ligne)
                            @php $pct = $total > 0 ? round(($ligne->cout_total / $total) * 100) : 0; @endphp
                            <tr>
                                <td class="fw-semibold">{{ $ligne->nom }}</td>
                                <td class="text-end">{{ number_format($ligne->total_contenants) }}</td>
                                <td class="text-end">{{ number_format($ligne->cout_unitaire, 0, ',', ' ') }} F</td>
                                <td class="text-end fw-bold text-success">{{ number_format($ligne->cout_total, 0, ',', ' ') }} F</td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <div class="progress flex-fill" style="height:6px;min-width:60px;">
                                            <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                                        </div>
                                        <span style="font-size:.8rem;">{{ $pct }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3">Total</th>
                                <th class="text-end text-success">{{ number_format($total, 0, ',', ' ') }} F</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Graphique sacs jaunes vs noirs --}}
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2 text-success"></i>Répartition Sacs Jaunes / Noirs
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <canvas id="sacChart" style="max-height:220px;"></canvas>
                <div class="mt-3 w-100">
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span><span class="badge" style="background:#D4A017;">&nbsp;</span> Sacs jaunes (DASRI)</span>
                        <strong>{{ number_format($sacJaune, 0, ',', ' ') }} F</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><span class="badge bg-secondary">&nbsp;</span> Sacs noirs (ménagers)</span>
                        <strong>{{ number_format($sacNoir, 0, ',', ' ') }} F</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Coût par service --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-diagram-3 me-2 text-success"></i>Coûts par service — Top consommateurs
            </div>
            <div class="card-body">
                @if($coutParService->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>Aucune donnée disponible
                    </div>
                @else
                @php $maxService = $coutParService->max('cout_total'); @endphp
                <div class="row g-2">
                    @foreach($coutParService as $i => $s)
                    @php $pct = $maxService > 0 ? round(($s->cout_total / $maxService) * 100) : 0; @endphp
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:28px;height:28px;border-radius:50%;background:{{ $i==0?'#1B6B3A':($i==1?'#2E8B57':'#e5e9ef') }};display:flex;align-items:center;justify-content:center;color:{{ $i<2?'#fff':'#6b7280' }};font-weight:700;font-size:.8rem;flex-shrink:0;">
                                {{ $i+1 }}
                            </div>
                            <div class="flex-fill">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-semibold" style="font-size:.88rem;">{{ $s->nom }}</span>
                                    <span class="fw-bold text-success" style="font-size:.88rem;">{{ number_format($s->cout_total, 0, ',', ' ') }} F</span>
                                </div>
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $i==0?'#1B6B3A':($i==1?'#2E8B57':'#6b7280') }};"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recommandations --}}
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header" style="background:#fffbeb;border-color:#fde68a;">
                <i class="bi bi-lightbulb me-2 text-warning"></i>Recommandations d'optimisation
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <i class="bi bi-1-circle-fill text-success mt-1 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold" style="font-size:.9rem;">Améliorer le tri à la source</div>
                                <small class="text-muted">Réduire le volume de sacs jaunes par une meilleure formation du personnel soignant. Économie estimée : <strong class="text-success">{{ number_format($economie, 0, ',', ' ') }} F/mois</strong>.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <i class="bi bi-2-circle-fill text-success mt-1 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold" style="font-size:.9rem;">Cibler les services prioritaires</div>
                                <small class="text-muted">Concentrer les actions sur les services à coûts élevés pour un impact maximal sur la réduction des déchets.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <i class="bi bi-3-circle-fill text-success mt-1 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold" style="font-size:.9rem;">Checklists régulières</div>
                                <small class="text-muted">Maintenir un score de conformité &ge;80% via des contrôles hebdomadaires pour éviter les amendes réglementaires.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
const ctx = document.getElementById('sacChart');
if(ctx) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Sacs jaunes (DASRI)', 'Sacs noirs (ménagers)'],
            datasets: [{
                data: [{{ $sacJaune }}, {{ $sacNoir }}],
                backgroundColor: ['#D4A017', '#6b7280'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + new Intl.NumberFormat('fr-FR').format(ctx.raw) + ' F'
                    }
                }
            },
            cutout: '65%'
        }
    });
}
</script>
@endpush
