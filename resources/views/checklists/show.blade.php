@extends('layouts.app')
@section('title', __('checklists.page_show_title'))
@section('content')
<div class="page-header"><h4>{{ __('checklists.header_show', ['date' => \Carbon\Carbon::parse($checklist->date_checklist)->format('d/m/Y')]) }}</h4></div>
<div class="row justify-content-center"><div class="col-md-7">
<div class="card"><div class="card-body p-4">
<div class="text-center mb-4">
    <div style="font-size:3rem;font-weight:800;color:{{ $checklist->score_conformite >= 80 ? '#1B6B3A' : ($checklist->score_conformite >= 60 ? '#D4A017' : '#C0392B') }}">{{ number_format($checklist->score_conformite,0) }}%</div>
    <div class="text-muted">{{ __('checklists.score_compliance') }}</div>
    <div class="progress mt-2" style="height:10px;">
        <div class="progress-bar {{ $checklist->score_conformite >= 80 ? 'bg-success' : ($checklist->score_conformite >= 60 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $checklist->score_conformite }}%"></div>
    </div>
</div>
@php $items = [
    'boites_fermees_75'           => 'show_item_boites',
    'sacs_correctement_etiquetes' => 'show_item_sacs',
    'local_ventile'               => 'show_item_local',
    'epi_port'                    => 'show_item_epi',
    'sacs_noirs_non_contamines'   => 'show_item_noirs',
    'contenants_integres'         => 'show_item_integres',
]; @endphp
@foreach($items as $key => $labelKey)
<div class="d-flex justify-content-between align-items-center p-2 border-bottom">
    <span>{{ __('checklists.' . $labelKey) }}</span>
    @if($checklist->$key)<span class="badge bg-success"><i class="bi bi-check"></i> {{ __('checklists.badge_conform') }}</span>@else<span class="badge bg-danger"><i class="bi bi-x"></i> {{ __('checklists.badge_nonconform') }}</span>@endif
</div>
@endforeach
@if($checklist->observations)
<div class="mt-3 p-3 bg-light rounded"><strong>{{ __('checklists.show_observations') }}</strong><br>{{ $checklist->observations }}</div>
@endif
<div class="mt-3 text-muted" style="font-size:.85rem;">{{ __('checklists.show_agent') }} : {{ $checklist->agent_nom }} — {{ __('checklists.show_service') }} : {{ $checklist->service_nom ?? __('checklists.show_service_general') }}</div>
</div></div>
</div></div>
@endsection
