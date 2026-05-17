@extends('layouts.app')
@section('title', __('superadmin.page_etab_detail_title', ['name' => $etab->nom]))
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-hospital me-2 text-success"></i>{{ $etab->nom }}</h4>
        <small class="text-muted">{{ __('superadmin.detail_subtitle', ['type' => ucfirst($etab->type), 'city' => $etab->ville, 'beds' => $etab->nombre_lits]) }}</small>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('superadmin.switch-tenant', $etab->id) }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-arrow-right-circle me-1"></i>{{ __('superadmin.btn_switch_to_struct') }}</button>
        </form>
        <a href="{{ route('superadmin.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('superadmin.btn_back') }}</a>
    </div>
</div>
@php
    $kpiMap = [
        'declarations' => ['kpi_label_decl',           'bi-clipboard-plus','#d1fae5','#065f46'],
        'poids'        => ['kpi_label_weight_kg',      'bi-weight',        '#dbeafe','#1e40af'],
        'score'        => ['kpi_label_compliance_pct', 'bi-shield-check',  '#f0fdf4','#166534'],
        'alertes'      => ['kpi_label_alerts',         'bi-bell',          '#fee2e2','#991b1b'],
        'users'        => ['kpi_label_users',          'bi-people',        '#ede9fe','#5b21b6'],
    ];
@endphp
<div class="row g-3 mb-4">
    @foreach($kpiMap as $key => [$labelKey, $icon, $bg, $color])
    <div class="col-6 col-md">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:{{ $bg }};color:{{ $color }};"><i class="bi {{ $icon }}"></i></div>
            <div class="value">{{ is_float($kpis[$key]) ? number_format($kpis[$key],1) : number_format($kpis[$key]) }}{{ $key==='score'?'%':'' }}</div>
            <div class="label">{{ __('superadmin.' . $labelKey) }}</div>
        </div>
    </div>
    @endforeach
</div>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-diagram-3 me-2 text-success"></i>{{ __('superadmin.card_services_with_count') }} ({{ count($services) }})</div>
            <div class="card-body p-0">
                @foreach($services as $s)
                <div class="px-3 py-2 border-bottom d-flex justify-content-between">
                    <span>{{ $s->nom }}</span>
                    <span class="badge {{ $s->actif ? 'bg-success' : 'bg-secondary' }}">{{ $s->actif ? __('superadmin.status_active') : __('superadmin.status_inactive') }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-people me-2 text-success"></i>{{ __('superadmin.card_team') }} ({{ count($users) }})</div>
            <div class="card-body p-0">
                @foreach($users as $u)
                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                    <span>{{ $u->prenom }} {{ $u->nom }}</span>
                    <span class="badge role-badge role-{{ $u->role }}">{{ $u->role }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
