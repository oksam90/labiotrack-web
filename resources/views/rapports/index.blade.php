@extends('layouts.app')
@section('title', __('rapports.page_index_title'))
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i>{{ __('rapports.header_index') }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('rapports.financier') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-graph-up me-1"></i>{{ __('rapports.btn_financial') }}
        </a>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGenerer">
            <i class="bi bi-plus me-1"></i>{{ __('rapports.btn_generate') }}
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('rapports.col_type') }}</th>
                    <th>{{ __('rapports.col_period') }}</th>
                    <th>{{ __('rapports.col_generated_at') }}</th>
                    <th>{{ __('rapports.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rapports as $r)
                <tr>
                    <td>
                        <span class="badge bg-light text-dark border">{{ __('rapports.type_' . $r->type) }}</span>
                    </td>
                    <td><small>{{ \Carbon\Carbon::parse($r->periode_debut)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($r->periode_fin)->format('d/m/Y') }}</small></td>
                    <td><small class="text-muted">{{ \Carbon\Carbon::parse($r->genere_at)->format('d/m/Y H:i') }}</small></td>
                    <td>
                        <a href="{{ route('rapports.pdf', $r->id) }}" class="btn btn-sm btn-outline-danger py-0">
                            <i class="bi bi-file-pdf me-1"></i>{{ __('rapports.btn_pdf') }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-5 text-muted"><i class="bi bi-inbox d-block fs-3 mb-2"></i>{{ __('rapports.empty_list') }}<br><small>{{ __('rapports.empty_hint') }}</small></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rapports->hasPages())
    <div class="card-footer bg-white">{{ $rapports->links() }}</div>
    @endif
</div>

{{-- Modal Générer rapport --}}
<div class="modal fade" id="modalGenerer" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('rapports.generer') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>{{ __('rapports.modal_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Sélecteur structure pour les utilisateurs globaux --}}
                    @if(Auth::user()->isGlobal() && ! ($currentTenant))
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('rapports.modal_structure') }} <span class="text-danger">*</span></label>
                        <select name="etablissement_id" class="form-select" required>
                            <option value="">{{ __('rapports.modal_structure_ph') }}</option>
                            @foreach(DB::table('etablissements')->where('actif',1)->orderBy('nom')->get() as $etab)
                                <option value="{{ $etab->id }}" {{ (old('etablissement_id') == $etab->id) ? 'selected' : '' }}>
                                    {{ $etab->nom }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            {!! __('rapports.modal_structure_hint', ['link' => '<a href="'.route('superadmin.index').'">'.__('rapports.modal_link_dashboard').'</a>']) !!}
                        </div>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('rapports.modal_type') }}</label>
                        <select name="type" class="form-select" required>
                            <option value="mensuel">{{ __('rapports.type_mensuel') }}</option>
                            <option value="trimestriel">{{ __('rapports.type_trimestriel') }}</option>
                            <option value="annuel">{{ __('rapports.type_annuel') }}</option>
                            <option value="ad_hoc">{{ __('rapports.type_ad_hoc_full') }}</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">{{ __('rapports.modal_date_start') }}</label>
                            <input type="date" name="periode_debut" class="form-control" value="{{ date('Y-m-01') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">{{ __('rapports.modal_date_end') }}</label>
                            <input type="date" name="periode_fin" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf me-2"></i>{{ __('rapports.btn_generate_pdf') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
