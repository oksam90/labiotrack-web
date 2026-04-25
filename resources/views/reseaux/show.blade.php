@extends('layouts.app')
@section('title', 'Réseau : ' . $reseau->nom)

@section('content')
<div class="topbar">
    <span class="topbar-title"><i class="bi bi-diagram-3 me-2"></i>{{ $reseau->nom }}</span>
    <div>
        <a href="{{ route('reseaux.edit', $reseau->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Modifier</a>
        <a href="{{ route('reseaux.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Retour</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <h6 class="text-muted small mb-2">INFORMATIONS</h6>
            <p class="mb-1"><strong>Statut :</strong>
                @if($reseau->actif)<span class="badge bg-success">Actif</span>@else<span class="badge bg-secondary">Inactif</span>@endif
            </p>
            @if($reseau->contact_email)<p class="mb-1"><i class="bi bi-envelope me-1"></i>{{ $reseau->contact_email }}</p>@endif
            @if($reseau->contact_telephone)<p class="mb-1"><i class="bi bi-telephone me-1"></i>{{ $reseau->contact_telephone }}</p>@endif
            @if($reseau->description)<hr><p class="text-muted small mb-0">{{ $reseau->description }}</p>@endif
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong><i class="bi bi-building me-2"></i>Établissements ({{ $reseau->etablissements->count() }})</strong></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead><tr><th class="ps-3">Nom</th><th>Type</th><th>Ville</th><th>Statut</th></tr></thead>
                    <tbody>
                    @forelse($reseau->etablissements as $e)
                        <tr>
                            <td class="ps-3 fw-bold">{{ $e->nom }}</td>
                            <td><span class="badge bg-info">{{ ucfirst($e->type) }}</span></td>
                            <td>{{ $e->ville }}</td>
                            <td>@if($e->actif)<span class="badge bg-success">Actif</span>@else<span class="badge bg-secondary">Inactif</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">Aucun établissement rattaché à ce réseau.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white"><strong><i class="bi bi-person-badge me-2"></i>AdminRéseau ({{ $reseau->admins->count() }})</strong></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead><tr><th class="ps-3">Nom</th><th>Email</th><th>Statut</th></tr></thead>
                    <tbody>
                    @forelse($reseau->admins as $u)
                        <tr>
                            <td class="ps-3 fw-bold">{{ $u->nom_complet }}</td>
                            <td>{{ $u->email }}</td>
                            <td>@if($u->actif)<span class="badge bg-success">Actif</span>@else<span class="badge bg-secondary">Inactif</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-4 text-muted">Aucun AdminRéseau désigné.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
