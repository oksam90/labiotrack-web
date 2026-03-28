@extends('layouts.app')
@section('title','Historique Conformité')
@section('content')
<div class="page-header"><h4>Historique des scores de conformité</h4></div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Mois</th><th>Score moyen</th><th>Nb checklists</th><th>Progression</th></tr></thead>
<tbody>
@foreach($historique as $h)
<tr>
    <td><strong>{{ $h->mois }}</strong></td>
    <td style="color:{{ $h->score_moyen >= 80 ? '#1B6B3A' : ($h->score_moyen >= 60 ? '#D4A017' : '#C0392B') }};font-weight:700;">{{ number_format($h->score_moyen,0) }}%</td>
    <td>{{ $h->nb }}</td>
    <td style="min-width:150px;"><div class="progress" style="height:8px;"><div class="progress-bar {{ $h->score_moyen >= 80 ? 'bg-success' : ($h->score_moyen >= 60 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $h->score_moyen }}%"></div></div></td>
</tr>
@endforeach
</tbody></table></div></div>
@endsection
