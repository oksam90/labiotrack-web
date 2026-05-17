@extends('layouts.app')
@section('title', __('declarations.page_show_title'))
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0">{{ __('declarations.page_show_title') }} #{{ $declaration->id }}</h4>
    <div class="d-flex gap-2">
        @if($declaration->statut === 'en_stock')
        <a href="{{ route('declarations.edit', $declaration->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i>{{ __('declarations.btn_edit') }}</a>
        @endif
        <span class="statut-badge statut-{{ $declaration->statut }} py-2 px-3">{{ __('declarations.status_' . $declaration->statut) }}</span>
    </div>
</div>
<div class="row g-3 justify-content-center">
<div class="col-md-7">
<div class="card"><div class="card-body p-4">
    <div class="row g-3">
        <div class="col-md-6"><div class="p-3 bg-light rounded"><small class="text-muted d-block">{{ __('declarations.show_service') }}</small><strong>{{ $declaration->service_nom }}</strong></div></div>
        <div class="col-md-6"><div class="p-3 bg-light rounded"><small class="text-muted d-block">{{ __('declarations.show_container') }}</small><strong>{{ $declaration->contenant_nom }}</strong></div></div>
        <div class="col-md-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block">{{ __('declarations.show_qty') }}</small><strong class="fs-4 text-success">{{ $declaration->nombre_contenants }}</strong></div></div>
        <div class="col-md-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block">{{ __('declarations.show_weight_estimated') }}</small><strong class="fs-5">{{ number_format($declaration->poids_estime_kg,2) }} kg</strong></div></div>
        <div class="col-md-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block">{{ __('declarations.show_weight_per_unit') }}</small><strong>{{ $declaration->poids_moyen_kg }} kg</strong></div></div>
        <div class="col-md-6"><div class="p-3 bg-light rounded"><small class="text-muted d-block">{{ __('declarations.show_datetime') }}</small><strong>{{ \Carbon\Carbon::parse($declaration->date_declaration)->format('d/m/Y') }}</strong> {{ substr($declaration->heure_declaration,0,5) }}</div></div>
        <div class="col-md-6"><div class="p-3 bg-light rounded"><small class="text-muted d-block">{{ __('declarations.show_agent') }}</small><strong>{{ $declaration->agent_nom }}</strong></div></div>
        @if($declaration->notes)
        <div class="col-12"><div class="p-3 bg-light rounded"><small class="text-muted d-block">{{ __('declarations.show_notes') }}</small>{{ $declaration->notes }}</div></div>
        @endif
        @if($declaration->qr_code)
        <div class="col-12 text-center">
            <div class="p-3 bg-light rounded">
                <small class="text-muted d-block mb-2">{{ __('declarations.show_qr_traceability') }}</small>
                <img src="{{ Storage::url($declaration->qr_code) }}" alt="QR Code" style="max-width:150px;">
            </div>
        </div>
        @endif
    </div>
    <div class="mt-3 p-3 rounded" style="background:#eff6ff;border:1px solid #bfdbfe;">
        <small><i class="bi bi-info-circle me-1 text-primary"></i><strong>{{ __('declarations.show_automated') }}</strong> {{ __('declarations.show_automated_detail', ['rate' => $declaration->poids_moyen_kg, 'count' => $declaration->nombre_contenants]) }}</small>
    </div>
</div></div>
<div class="mt-3"><a href="{{ route('declarations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>{{ __('declarations.btn_back') }}</a></div>
</div></div>
@endsection
