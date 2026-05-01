@extends('layouts.app')

@section('title', 'Historique des signatures')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">
        <i class="bi bi-pen"></i> Historique des signatures
    </h2>
</div>

{{-- ── Filtres ─────────────────────────────────────────────── --}}
<form method="GET" class="card card-body mb-3">
    <div class="row g-2">
        @if($etablissementsFiltre->isNotEmpty())
        <div class="col-md-3">
            <label class="form-label small mb-1">Établissement</label>
            <select name="etablissement_id" class="form-select form-select-sm">
                <option value="">— Tous —</option>
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
            <label class="form-label small mb-1">Du</label>
            <input type="date" name="date_debut" class="form-control form-control-sm"
                   value="{{ request('date_debut') }}" />
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Au</label>
            <input type="date" name="date_fin" class="form-control form-control-sm"
                   value="{{ request('date_fin') }}" />
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Statut PDF</label>
            <select name="statut_pdf" class="form-select form-select-sm">
                <option value="">— Tous —</option>
                <option value="genere"  {{ request('statut_pdf')==='genere'  ? 'selected':'' }}>Généré</option>
                <option value="attente" {{ request('statut_pdf')==='attente' ? 'selected':'' }}>En attente</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button class="btn btn-sm btn-success">
                <i class="bi bi-funnel"></i> Filtrer
            </button>
            <a href="{{ route('signatures.index') }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-clockwise"></i> Réinitialiser
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
                    <th>Bordereau</th>
                    <th>Établissement</th>
                    <th>Signataire</th>
                    <th>Date signature</th>
                    <th>PDF</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
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
                                <i class="bi bi-check"></i> Généré
                            </span>
                        @else
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-hourglass-split"></i> En cours
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($s->revoked_at)
                            <span class="badge bg-danger">Révoquée</span>
                        @else
                            <span class="badge bg-primary">Active</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('signatures.show', $s->id) }}"
                               class="btn btn-outline-secondary" title="Détails preuve">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($s->pdf_generated_at)
                            <a href="{{ route('signatures.pdf', $s->id) }}"
                               class="btn btn-outline-success" title="Télécharger PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox"></i> Aucune signature enregistrée
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
