@extends('layouts.app')

@section('title', __('signatures.page_index_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">
        <i class="bi bi-pen"></i> {{ __('signatures.header_index') }}
    </h2>
</div>

{{-- ── Filtres ─────────────────────────────────────────────── --}}
<form method="GET" class="card card-body mb-3">
    <div class="row g-2">
        @if($etablissementsFiltre->isNotEmpty())
        <div class="col-md-3">
            <label class="form-label small mb-1">{{ __('signatures.filter_etab') }}</label>
            <select name="etablissement_id" class="form-select form-select-sm">
                <option value="">{{ __('signatures.filter_all') }}</option>
                @foreach($etablissementsFiltre as $e)
                    <option value="{{ $e->id }}"
                        {{ request('etablissement_id') == $e->id ? 'selected' : '' }}>
                        {{ $e->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-md-2">
            <label class="form-label small mb-1">{{ __('signatures.filter_from') }}</label>
            <input type="date" name="date_debut" class="form-control form-control-sm"
                   value="{{ request('date_debut') }}" />
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">{{ __('signatures.filter_to') }}</label>
            <input type="date" name="date_fin" class="form-control form-control-sm"
                   value="{{ request('date_fin') }}" />
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">{{ __('signatures.filter_pdf_status') }}</label>
            <select name="statut_pdf" class="form-select form-select-sm">
                <option value="">{{ __('signatures.filter_all') }}</option>
                <option value="genere"  {{ request('statut_pdf')==='genere'  ? 'selected':'' }}>{{ __('signatures.filter_pdf_generated') }}</option>
                <option value="attente" {{ request('statut_pdf')==='attente' ? 'selected':'' }}>{{ __('signatures.filter_pdf_pending') }}</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button class="btn btn-sm btn-success">
                <i class="bi bi-funnel"></i> {{ __('signatures.btn_filter') }}
            </button>
            <a href="{{ route('signatures.index') }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-clockwise"></i> {{ __('signatures.btn_reset') }}
            </a>
        </div>
    </div>
</form>

{{-- ── Liste ──────────────────────────────────────────────── --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('signatures.col_bordereau') }}</th>
                    <th>{{ __('signatures.col_etab') }}</th>
                    <th>{{ __('signatures.col_signer') }}</th>
                    <th>{{ __('signatures.col_signed_at') }}</th>
                    <th>{{ __('signatures.col_pdf') }}</th>
                    <th>{{ __('signatures.col_status') }}</th>
                    <th class="text-end">{{ __('signatures.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($signatures as $s)
                <tr class="{{ $s->revoked_at ? 'table-danger' : '' }}">
                    <td>
                        <a href="{{ route('collectes.show', $s->collecte_id) }}"
                           class="text-decoration-none fw-bold">
                            {{ $s->collecte->numero_bordereau ?? '#'.$s->collecte_id }}
                        </a>
                    </td>
                    <td>{{ $s->etablissement->nom ?? '—' }}</td>
                    <td>
                        {{ $s->signataire_nom }}
                        @if($s->signataire_fonction)
                            <br><small class="text-muted">{{ $s->signataire_fonction }}</small>
                        @endif
                    </td>
                    <td>{{ $s->signed_at?->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($s->pdf_generated_at)
                            <span class="badge bg-success">
                                <i class="bi bi-check"></i> {{ __('signatures.badge_generated') }}
                            </span>
                        @else
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-hourglass-split"></i> {{ __('signatures.badge_pending') }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($s->revoked_at)
                            <span class="badge bg-danger">{{ __('signatures.badge_revoked') }}</span>
                        @else
                            <span class="badge bg-primary">{{ __('signatures.badge_active') }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('signatures.show', $s->id) }}"
                               class="btn btn-outline-secondary" title="{{ __('signatures.action_details') }}">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($s->pdf_generated_at)
                            <a href="{{ route('signatures.pdf', $s->id) }}"
                               class="btn btn-outline-success" title="{{ __('signatures.action_download_pdf') }}">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox"></i> {{ __('signatures.empty_list') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $signatures->links() }}
</div>
@endsection
