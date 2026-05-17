@extends('layouts.app')
@section('title', __('superadmin.page_comparatif_title'))
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-success"></i>{{ __('superadmin.header_comparatif') }}</h4>
        <small class="text-muted">{{ __('superadmin.subtitle_comparatif', ['month' => \Carbon\Carbon::createFromFormat('Y-m',$mois)->locale(app()->getLocale())->translatedFormat('F Y')]) }}</small>
    </div>
    <a href="{{ route('superadmin.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('superadmin.btn_back') }}</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart me-2 text-success"></i>{{ __('superadmin.card_production') }}</div>
            <div class="card-body"><canvas id="chartPoids" height="200"
                data-label-weight="{{ __('superadmin.chart_label_weight') }}"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-shield-check me-2 text-success"></i>{{ __('superadmin.card_score_compliance') }}</div>
            <div class="card-body"><canvas id="chartScore" height="200"
                data-label-score="{{ __('superadmin.chart_label_score') }}"></canvas></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-currency-dollar me-2 text-warning"></i>{{ __('superadmin.card_costs_by_struct') }}</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr>
                <th>{{ __('superadmin.col_struct') }}</th>
                <th>{{ __('superadmin.col_type') }}</th>
                <th class="text-end">{{ __('superadmin.col_weight') }}</th>
                <th class="text-end">{{ __('superadmin.col_cost_estimate') }}</th>
                <th>{{ __('superadmin.col_score_compliance') }}</th>
            </tr></thead>
            <tbody>
                @php $maxPoids = $etabs->max('poids') ?: 1; @endphp
                @foreach($etabs as $e)
                <tr>
                    <td class="fw-semibold">{{ $e->nom }}</td>
                    <td><span class="badge bg-light text-dark border">{{ ucfirst($e->type) }}</span></td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <div class="progress flex-fill" style="height:6px;max-width:100px;">
                                <div class="progress-bar bg-success" style="width:{{ $maxPoids > 0 ? ($e->poids/$maxPoids)*100 : 0 }}%"></div>
                            </div>
                            {{ number_format($e->poids,1) }} kg
                        </div>
                    </td>
                    <td class="text-end fw-bold text-warning">{{ number_format($e->cout,0,',',' ') }} F</td>
                    <td>
                        @php $sc = round($e->score); @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-fill" style="height:8px;">
                                <div class="progress-bar {{ $sc>=80?'bg-success':($sc>=60?'bg-warning':'bg-danger') }}" style="width:{{ $sc }}%"></div>
                            </div>
                            <span style="font-size:.82rem;">{{ $sc }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $etabs->links() }}</div>
</div>
@endsection
@push('scripts')
<script>
const labels = @json($etabs->pluck('nom'));
const poids  = @json($etabs->pluck('poids'));
const scores = @json($etabs->pluck('score')->map(fn($s) => round($s)));
const colors = ['#1B6B3A','#2E8B57','#D4A017','#2980B9','#8e44ad','#E67E22'];

const chartPoids = document.getElementById('chartPoids');
new Chart(chartPoids, {
    type:'bar',
    data:{labels,datasets:[{label:chartPoids.dataset.labelWeight,data:poids,backgroundColor:colors,borderRadius:6}]},
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true},x:{grid:{display:false}}}}
});

const chartScore = document.getElementById('chartScore');
new Chart(chartScore, {
    type:'radar',
    data:{labels,datasets:[{label:chartScore.dataset.labelScore,data:scores,
        backgroundColor:'rgba(27,107,58,.15)',borderColor:'#1B6B3A',pointBackgroundColor:'#1B6B3A',borderWidth:2}]},
    options:{responsive:true,scales:{r:{beginAtZero:true,max:100,ticks:{stepSize:20}}}}
});
</script>
@endpush
