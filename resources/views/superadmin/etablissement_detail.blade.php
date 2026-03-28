@extends('layouts.app')
@section('title', $etab->nom . ' — Fiche structure')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-hospital me-2 text-success"></i>{{ $etab->nom }}</h4>
        <small class="text-muted">{{ ucfirst($etab->type) }} — {{ $etab->ville }} | {{ $etab->nombre_lits }} lits</small>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('superadmin.switch-tenant', $etab->id) }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-arrow-right-circle me-1"></i>Basculer vers cette structure</button>
        </form>
        <a href="{{ route('superadmin.index') }}" class="btn btn-sm btn-outline-secondary">← Retour</a>
    </div>
</div>
<div class="row g-3 mb-4">
    @foreach(['declarations'=>['Déclarations','bi-clipboard-plus','#d1fae5','#065f46'],'poids'=>['Poids (kg)','bi-weight','#dbeafe','#1e40af'],'score'=>['Conformité %','bi-shield-check','#f0fdf4','#166534'],'alertes'=>['Alertes','bi-bell','#fee2e2','#991b1b'],'users'=>['Utilisateurs','bi-people','#ede9fe','#5b21b6']] as $key=>[$label,$icon,$bg,$color])
    <div class="col-6 col-md">
        <div class="stat-card text-center">
            <div class="icon mx-auto mb-2" style="background:{{ $bg }};color:{{ $color }};"><i class="bi {{ $icon }}"></i></div>
            <div class="value">{{ is_float($kpis[$key]) ? number_format($kpis[$key],1) : number_format($kpis[$key]) }}{{ $key==='score'?'%':'' }}</div>
            <div class="label">{{ $label }}</div>
        </div>
    </div>
    @endforeach
</div>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-diagram-3 me-2 text-success"></i>Services ({{ count($services) }})</div>
            <div class="card-body p-0">
                @foreach($services as $s)
                <div class="px-3 py-2 border-bottom d-flex justify-content-between">
                    <span>{{ $s->nom }}</span>
                    <span class="badge {{ $s->actif ? 'bg-success' : 'bg-secondary' }}">{{ $s->actif ? 'Actif' : 'Inactif' }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-people me-2 text-success"></i>Équipe ({{ count($users) }})</div>
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
