@extends('layouts.app')
@section('title', __('destructions.page_show_title'))
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-fire me-2 text-danger"></i>{{ __('destructions.header_show') }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('destructions.certificat', $destruction->id) }}" class="btn btn-success btn-sm">
            <i class="bi bi-award me-1"></i>{{ __('destructions.btn_view_cert') }}
        </a>
        <a href="{{ route('destructions.certificat.pdf', $destruction->id) }}" class="btn btn-danger btn-sm">
            <i class="bi bi-file-pdf me-1"></i>{{ __('destructions.btn_download_pdf') }}
        </a>
        <a href="{{ route('destructions.index') }}" class="btn btn-light btn-sm">{{ __('destructions.btn_back') }}</a>
    </div>
</div>

@php
    $methodKey = match($destruction->methode){'incineration'=>'incineration','autoclave'=>'autoclave','desinfection_chimique'=>'desinfection','autre'=>'other',default=>'other'};
@endphp

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2"></i>{{ __('destructions.show_card_info') }}</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted w-50">{{ __('destructions.show_cert_num') }}</td><td><code class="fw-bold">{{ $destruction->certificat_numero }}</code></td></tr>
                    <tr><td class="text-muted">{{ __('destructions.show_bordereau') }}</td><td>{{ $destruction->numero_bordereau }}</td></tr>
                    <tr><td class="text-muted">{{ __('destructions.show_method') }}</td><td>{{ __('destructions.method_' . $methodKey) }}</td></tr>
                    <tr><td class="text-muted">{{ __('destructions.show_site') }}</td><td>{{ $destruction->site_traitement ?? '—' }}</td></tr>
                    <tr><td class="text-muted">{{ __('destructions.show_date_reception') }}</td><td>{{ $destruction->date_reception ? \Carbon\Carbon::parse($destruction->date_reception)->format('d/m/Y') : '—' }}</td></tr>
                    <tr><td class="text-muted">{{ __('destructions.show_date_destruction') }}</td><td>{{ \Carbon\Carbon::parse($destruction->date_destruction)->format('d/m/Y') }}</td></tr>
                    <tr><td class="text-muted">{{ __('destructions.show_weight_real') }}</td><td><strong>{{ number_format($destruction->poids_reel_kg, 1) }} kg</strong></td></tr>
                    <tr><td class="text-muted">{{ __('destructions.show_conform') }}</td><td><span class="badge bg-{{ $destruction->conforme ? 'success' : 'danger' }}">{{ $destruction->conforme ? __('destructions.conform_yes') : __('destructions.conform_no') }}</span></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-building me-2"></i>{{ __('destructions.show_etab') }}</div>
            <div class="card-body">
                <p class="mb-1 fw-semibold">{{ $etablissement->nom }}</p>
                <p class="mb-1 text-muted">{{ $etablissement->adresse }}</p>
                <p class="mb-0 text-muted">{{ $etablissement->ville }}</p>
            </div>
        </div>
    </div>
    @if($destruction->notes)
    <div class="col-12">
        <div class="card">
            <div class="card-header fw-semibold">{{ __('destructions.show_notes') }}</div>
            <div class="card-body">{{ $destruction->notes }}</div>
        </div>
    </div>
    @endif
</div>
@endsection
