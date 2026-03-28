@extends('layouts.app')
@section('title', 'Rapports')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i>Rapports générés</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('rapports.financier') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-graph-up me-1"></i>Analyse financière
        </a>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGenerer">
            <i class="bi bi-plus me-1"></i>Générer un rapport
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
                <tr><th>Type</th><th>Période</th><th>Généré le</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($rapports as $r)
                <tr>
                    <td>
                        @php $types=['mensuel'=>'📅 Mensuel','trimestriel'=>'📊 Trimestriel','annuel'=>'📆 Annuel','ad_hoc'=>'🔍 Ad hoc']; @endphp
                        <span class="badge bg-light text-dark border">{{ $types[$r->type] ?? $r->type }}</span>
                    </td>
                    <td><small>{{ \Carbon\Carbon::parse($r->periode_debut)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($r->periode_fin)->format('d/m/Y') }}</small></td>
                    <td><small class="text-muted">{{ \Carbon\Carbon::parse($r->genere_at)->format('d/m/Y H:i') }}</small></td>
                    <td>
                        <a href="{{ route('rapports.pdf', $r->id) }}" class="btn btn-sm btn-outline-danger py-0">
                            <i class="bi bi-file-pdf me-1"></i>PDF
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-5 text-muted"><i class="bi bi-inbox d-block fs-3 mb-2"></i>Aucun rapport généré.<br><small>Cliquez sur "Générer un rapport" pour commencer.</small></td></tr>
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
                    <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>Générer un rapport</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Sélecteur structure pour les utilisateurs globaux --}}
                    @if(Auth::user()->isGlobal() && ! ($currentTenant))
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Structure concernée <span class="text-danger">*</span></label>
                        <select name="etablissement_id" class="form-select" required>
                            <option value="">— Sélectionner une structure —</option>
                            @foreach(DB::table('etablissements')->where('actif',1)->orderBy('nom')->get() as $etab)
                                <option value="{{ $etab->id }}" {{ (old('etablissement_id') == $etab->id) ? 'selected' : '' }}>
                                    {{ $etab->nom }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Ou basculez vers une structure via le <a href="{{ route('superadmin.index') }}">dashboard réseau</a>.
                        </div>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type de rapport</label>
                        <select name="type" class="form-select" required>
                            <option value="mensuel">📅 Mensuel</option>
                            <option value="trimestriel">📊 Trimestriel</option>
                            <option value="annuel">📆 Annuel</option>
                            <option value="ad_hoc">🔍 Période personnalisée</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Date début</label>
                            <input type="date" name="periode_debut" class="form-control" value="{{ date('Y-m-01') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Date fin</label>
                            <input type="date" name="periode_fin" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Générer le PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
