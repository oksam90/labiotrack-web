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
<div class="col-md-8">
<div class="card"><div class="card-body p-4">

    {{-- Détail des lignes (service × contenant) --}}
    <div class="table-responsive mb-3">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('declarations.show_service') }}</th>
                    <th>{{ __('declarations.show_container') }}</th>
                    <th class="text-end">{{ __('declarations.show_qty') }}</th>
                    <th class="text-end">{{ __('declarations.show_weight_per_unit') }}</th>
                    <th class="text-end">{{ __('declarations.show_weight_estimated') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($declaration->lignes as $ligne)
                <tr>
                    <td><span class="badge bg-light text-dark border">{{ $ligne->service->nom ?? '—' }}</span></td>
                    <td><small>{{ $ligne->typeContenant->nom ?? '—' }}</small></td>
                    <td class="text-end fw-semibold text-success">{{ $ligne->nombre_contenants }}</td>
                    <td class="text-end"><small>{{ $ligne->typeContenant->poids_moyen_kg ?? 0 }} kg</small></td>
                    <td class="text-end fw-bold">{{ number_format($ligne->poids_estime_kg, 2) }} kg</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="2" class="text-end">{{ __('declarations.total_containers') }}</th>
                    <th class="text-end text-success">{{ $declaration->nombre_contenants }}</th>
                    <th></th>
                    <th class="text-end text-primary">{{ number_format($declaration->poids_estime_kg, 2) }} kg</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="row g-3">
        <div class="col-md-6"><div class="p-3 bg-light rounded"><small class="text-muted d-block">{{ __('declarations.show_datetime') }}</small><strong>{{ \Carbon\Carbon::parse($declaration->date_declaration)->format('d/m/Y') }}</strong> {{ substr($declaration->heure_declaration,0,5) }}</div></div>
        <div class="col-md-6"><div class="p-3 bg-light rounded"><small class="text-muted d-block">{{ __('declarations.show_agent') }}</small><strong>{{ $declaration->user->nom_complet ?? '—' }}</strong></div></div>
        @if($declaration->notes)
        <div class="col-12"><div class="p-3 bg-light rounded"><small class="text-muted d-block">{{ __('declarations.show_notes') }}</small>{{ $declaration->notes }}</div></div>
        @endif
        @if($declaration->qr_code)
        <div class="col-12 text-center">
            <div class="p-3 bg-light rounded">
                <small class="text-muted d-block mb-2">{{ __('declarations.show_qr_traceability') }}</small>
                <img src="{{ Storage::url($declaration->qr_code) }}" alt="QR Code" loading="lazy" style="max-width:150px;">
            </div>
        </div>
        @endif
    </div>
    <div class="mt-3 p-3 rounded" style="background:#eff6ff;border:1px solid #bfdbfe;">
        <small><i class="bi bi-info-circle me-1 text-primary"></i><strong>{{ __('declarations.show_automated') }}</strong> {{ __('declarations.show_automated_multi') }}</small>
    </div>
</div></div>
<div class="mt-3"><a href="{{ route('declarations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>{{ __('declarations.btn_back') }}</a></div>
</div></div>
@endsection
