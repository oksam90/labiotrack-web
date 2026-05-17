@extends('layouts.app')
@section('title', __('collectes.page_create_title'))
@section('content')
<div class="page-header"><h4 class="fw-bold mb-0"><i class="bi bi-truck me-2 text-primary"></i>{{ __('collectes.header_create') }}</h4></div>
<div class="row justify-content-center"><div class="col-md-8">
<div class="card"><div class="card-body p-4">
<form method="POST" action="{{ route('collectes.store') }}" enctype="multipart/form-data">@csrf
<div class="mb-4">
    <label class="form-label fw-bold">{{ __('collectes.form_decl_available') }}</label>
    <small class="text-muted d-block mb-2">{{ __('collectes.form_decl_check_hint') }}</small>
    <div class="table-responsive">
    <table class="table table-sm">
    <thead><tr>
        <th></th>
        <th>{{ __('collectes.col_service') }}</th>
        <th>{{ __('collectes.col_container_type') }}</th>
        <th>{{ __('collectes.col_qty') }}</th>
        <th>{{ __('collectes.col_weight_estimated') }}</th>
        <th>{{ __('collectes.col_date') }}</th>
    </tr></thead>
    <tbody>
    @foreach($declarationsDisponibles as $d)
    <tr>
        <td><input type="checkbox" name="declarations[]" value="{{ $d->id }}" class="form-check-input"></td>
        <td>{{ $d->service_nom }}</td>
        <td><small>{{ $d->contenant_nom }}</small></td>
        <td><strong>{{ $d->nombre_contenants }}</strong></td>
        <td>{{ number_format($d->poids_estime_kg,1) }} kg</td>
        <td><small>{{ \Carbon\Carbon::parse($d->date_declaration)->format('d/m/Y') }}</small></td>
    </tr>
    @endforeach
    @if($declarationsDisponibles->isEmpty())
    <tr><td colspan="6" class="text-center text-muted py-3">{{ __('collectes.form_no_decl_available') }}</td></tr>
    @endif
    </tbody>
    </table>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">{{ __('collectes.form_collecteur') }}</label>
        <select name="collecteur_id" class="form-select">
            <option value="">{{ __('collectes.form_select_placeholder') }}</option>
            @foreach($collecteurs as $c)<option value="{{ $c->id }}">{{ $c->prenom }} {{ $c->nom }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('collectes.form_vehicle') }}</label>
        <input type="text" name="vehicule" class="form-control" placeholder="{{ __('collectes.form_vehicle_placeholder') }}">
    </div>
</div>
<div class="mb-3"><label class="form-label">{{ __('collectes.form_photo') }}</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
<div class="mb-4"><label class="form-label">{{ __('collectes.form_notes') }}</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
<div class="d-flex gap-2">
<button type="submit" class="btn btn-primary flex-fill py-2"><i class="bi bi-truck me-2"></i>{{ __('collectes.btn_create_and_pdf') }}</button>
<a href="{{ route('collectes.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
</div>
</form></div></div>
</div></div>
@endsection
