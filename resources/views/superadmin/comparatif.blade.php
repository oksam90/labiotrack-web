@extends('layouts.app')
@section('title','Analyse comparative inter-structures')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-success"></i>Analyse comparative</h4>
        <small class="text-muted">{{ \Carbon\Carbon::createFromFormat('Y-m',$mois)->translatedFormat('F Y') }} — Toutes structures</small>
    </div>
    <a href="{{ route('superadmin.index') }}" class="btn btn-sm btn-outline-secondary">← Retour</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart me-2 text-success"></i>Production de déchets (kg)</div>
            <div class="card-body"><canvas id="chartPoids" height="200"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-shield-check me-2 text-success"></i>Score de conformité (%)</div>
            <div class="card-body"><canvas id="chartScore" height="200"></canvas></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-currency-dollar me-2 text-warning"></i>Coûts estimés par structure</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Structure</th><th>Type</th><th class="text-end">Poids (kg)</th><th class="text-end">Coût estimé</th><th>Score conformité</th></tr></thead>
            <tbody>
                @php $maxPoids = $donnees->max('poids') ?: 1; @endphp
                @foreach($donnees as $d)
                <tr>
                    <td class="fw-semibold">{{ $d['etab']->nom }}</td>
                    <td><span class="badge bg-light text-dark border">{{ ucfirst($d['etab']->type) }}</span></td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <div class="progress flex-fill" style="height:6px;max-width:100px;">
                                <div class="progress-bar bg-success" style="width:{{ $maxPoids > 0 ? ($d['poids']/$maxPoids)*100 : 0 }}%"></div>
                            </div>
                            {{ number_format($d['poids'],1) }} kg
                        </div>
                    </td>
                    <td class="text-end fw-bold text-warning">{{ number_format($d['cout'],0,',',' ') }} F</td>
                    <td>
                        @php $sc = round($d['score']); @endphp
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
</div>
@endsection
@push('scripts')
<script>
const labels = @json($donnees->pluck('etab')->pluck('nom'));
const poids  = @json($donnees->pluck('poids'));
const scores = @json($donnees->map(fn($d)=>round($d['score'])));
const colors = ['#1B6B3A','#2E8B57','#D4A017','#2980B9','#8e44ad','#E67E22'];

new Chart(document.getElementById('chartPoids'),{
    type:'bar',data:{labels,datasets:[{label:'Poids (kg)',data:poids,backgroundColor:colors,borderRadius:6}]},
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true},x:{grid:{display:false}}}}
});
new Chart(document.getElementById('chartScore'),{
    type:'radar',
    data:{labels,datasets:[{label:'Score (%)',data:scores,
        backgroundColor:'rgba(27,107,58,.15)',borderColor:'#1B6B3A',pointBackgroundColor:'#1B6B3A',borderWidth:2}]},
    options:{responsive:true,scales:{r:{beginAtZero:true,max:100,ticks:{stepSize:20}}}}
});
</script>
@endpush
