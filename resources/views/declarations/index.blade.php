{{-- declarations/index.blade.php --}}
@extends('layouts.app')
@section('title', __('declarations.page_index_title'))
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-0">{{ __('declarations.header_index') }}</h4>
        <small class="text-muted">{{ __('declarations.subtitle_index') }}</small>
    </div>
    <a href="{{ route('declarations.create') }}" class="btn btn-primary"><i class="bi bi-plus me-1"></i>{{ __('declarations.btn_new') }}</a>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.8rem;">{{ __('declarations.filter_service') }}</label>
                <select name="service_id" class="form-select form-select-sm">
                    <option value="">{{ __('declarations.filter_all') }}</option>
                    @foreach($services as $s)<option value="{{ $s->id }}" {{ request('service_id')==$s->id?'selected':'' }}>{{ $s->nom }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.8rem;">{{ __('declarations.filter_container') }}</label>
                <select name="type_contenant_id" class="form-select form-select-sm">
                    <option value="">{{ __('declarations.filter_all') }}</option>
                    @foreach($typeContenants as $tc)<option value="{{ $tc->id }}" {{ request('type_contenant_id')==$tc->id?'selected':'' }}>{{ $tc->nom }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.8rem;">{{ __('declarations.filter_status') }}</label>
                <select name="statut" class="form-select form-select-sm">
                    <option value="">{{ __('declarations.filter_all') }}</option>
                    <option value="en_stock" {{ request('statut')=='en_stock'?'selected':'' }}>{{ __('declarations.status_en_stock') }}</option>
                    <option value="en_transport" {{ request('statut')=='en_transport'?'selected':'' }}>{{ __('declarations.status_en_transport') }}</option>
                    <option value="detruit" {{ request('statut')=='detruit'?'selected':'' }}>{{ __('declarations.status_detruit') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.8rem;">{{ __('declarations.filter_from') }}</label>
                <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.8rem;">{{ __('declarations.filter_to') }}</label>
                <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-funnel"></i> {{ __('declarations.btn_filter') }}</button>
                <a href="{{ route('declarations.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('declarations.col_date') }}</th>
                    <th>{{ __('declarations.col_service') }}</th>
                    <th>{{ __('declarations.col_container') }}</th>
                    <th>{{ __('declarations.col_qty') }}</th>
                    <th>{{ __('declarations.col_weight_est') }}</th>
                    <th>{{ __('declarations.col_status') }}</th>
                    <th>{{ __('declarations.col_agent') }}</th>
                    <th>{{ __('declarations.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($declarations as $d)
                <tr>
                    <td><small class="text-muted">{{ $d->id }}</small></td>
                    <td>
                        <div>{{ \Carbon\Carbon::parse($d->date_declaration)->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ substr($d->heure_declaration,0,5) }}</small>
                    </td>
                    <td>
                        @foreach($d->lignes->pluck('service.nom')->filter()->unique() as $svc)
                        <span class="badge bg-light text-dark border mb-1">{{ $svc }}</span>
                        @endforeach
                    </td>
                    <td>
                        @foreach($d->lignes->pluck('typeContenant.nom')->filter()->unique() as $tc)
                        <small class="d-block">{{ $tc }}</small>
                        @endforeach
                    </td>
                    <td><strong>{{ $d->nombre_contenants }}</strong></td>
                    <td>{{ number_format($d->poids_estime_kg,1) }} kg</td>
                    <td><span class="statut-badge statut-{{ $d->statut }}">{{ __('declarations.status_' . $d->statut) }}</span></td>
                    <td><small class="text-muted">{{ $d->user->nom_complet ?? '—' }}</small></td>
                    <td>
                        <a href="{{ route('declarations.show', $d->id) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2"><i class="bi bi-eye"></i></a>
                        @if($d->statut === 'en_stock')
                        <a href="{{ route('declarations.edit', $d->id) }}" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-2"><i class="bi bi-pencil"></i></a>
                        @endif
                    </td>
                </tr>
                @endforeach
                @if($declarations->isEmpty())
                <tr><td colspan="9" class="text-center text-muted py-4">{{ __('declarations.empty_list') }}</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">{{ $declarations->total() }} {{ __('declarations.col_results') }}</small>
        {{ $declarations->withQueryString()->links() }}
    </div>
</div>
@endsection
