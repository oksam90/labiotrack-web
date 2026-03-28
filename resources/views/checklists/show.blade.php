@extends('layouts.app')
@section('title','Checklist')
@section('content')
<div class="page-header"><h4>Checklist du {{ \Carbon\Carbon::parse($checklist->date_checklist)->format('d/m/Y') }}</h4></div>
<div class="row justify-content-center"><div class="col-md-7">
<div class="card"><div class="card-body p-4">
<div class="text-center mb-4">
    <div style="font-size:3rem;font-weight:800;color:{{ $checklist->score_conformite >= 80 ? '#1B6B3A' : ($checklist->score_conformite >= 60 ? '#D4A017' : '#C0392B') }}">{{ number_format($checklist->score_conformite,0) }}%</div>
    <div class="text-muted">Score de conformité</div>
    <div class="progress mt-2" style="height:10px;">
        <div class="progress-bar {{ $checklist->score_conformite >= 80 ? 'bg-success' : ($checklist->score_conformite >= 60 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $checklist->score_conformite }}%"></div>
    </div>
</div>
@php $items = ['boites_fermees_75'=>'Boîtes fermées aux 3/4','sacs_correctement_etiquetes'=>'Sacs correctement étiquetés','local_ventile'=>'Local ventilé','epi_port'=>'Port des EPI','sacs_noirs_non_contamines'=>'Sacs noirs non contaminés','contenants_integres'=>'Contenants intègres']; @endphp
@foreach($items as $key => $label)
<div class="d-flex justify-content-between align-items-center p-2 border-bottom">
    <span>{{ $label }}</span>
    @if($checklist->$key)<span class="badge bg-success"><i class="bi bi-check"></i> Conforme</span>@else<span class="badge bg-danger"><i class="bi bi-x"></i> Non conforme</span>@endif
</div>
@endforeach
@if($checklist->observations)
<div class="mt-3 p-3 bg-light rounded"><strong>Observations :</strong><br>{{ $checklist->observations }}</div>
@endif
<div class="mt-3 text-muted" style="font-size:.85rem;">Agent : {{ $checklist->agent_nom }} — Service : {{ $checklist->service_nom ?? 'Général' }}</div>
</div></div>
</div></div>
@endsection
