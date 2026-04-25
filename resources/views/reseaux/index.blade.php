@extends('layouts.app')
@section('title','Réseaux')

@section('content')
<div class="topbar">
    <span class="topbar-title"><i class="bi bi-diagram-3 me-2"></i>Gestion des Réseaux</span>
    <a href="{{ route('reseaux.create') }}" class="btn btn-sm" style="background:var(--primary);color:#fff;border-radius:8px">
        <i class="bi bi-plus-lg me-1"></i>Nouveau réseau
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.875rem">
            <thead style="background:#f8faf9">
                <tr>
                    <th class="ps-3">Nom du réseau</th>
                    <th>Description</th>
                    <th>Établissements</th>
                    <th>Utilisateurs</th>
                    <th>Contact</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($reseaux as $r)
                <tr>
                    <td class="ps-3 fw-bold">{{ $r->nom }}</td>
                    <td class="text-muted small">{{ Str::limit($r->description, 50) }}</td>
                    <td><span class="badge bg-info">{{ $r->etablissements_count }}</span></td>
                    <td><span class="badge bg-secondary">{{ $r->users_count }}</span></td>
                    <td class="small">
                        @if($r->contact_email)<i class="bi bi-envelope me-1"></i>{{ $r->contact_email }}<br>@endif
                        @if($r->contact_telephone)<i class="bi bi-telephone me-1"></i>{{ $r->contact_telephone }}@endif
                    </td>
                    <td>
                        @if($r->actif)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-secondary">Inactif</span>
                        @endif
                    </td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('reseaux.show', $r->id) }}" class="btn btn-sm btn-outline-primary py-0" title="Voir"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('reseaux.edit', $r->id) }}" class="btn btn-sm btn-outline-secondary py-0" title="Modifier"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('reseaux.toggle', $r->id) }}" class="d-inline">@csrf
                            <button type="submit" class="btn btn-sm {{ $r->actif ? 'btn-outline-warning' : 'btn-outline-success' }} py-0" title="{{ $r->actif ? 'Désactiver' : 'Activer' }}">
                                <i class="bi {{ $r->actif ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('reseaux.destroy', $r->id) }}" class="d-inline"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce réseau ? Action irréversible.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0" title="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-diagram-3 display-6 d-block mb-2"></i>
                    Aucun réseau enregistré.
                    <a href="{{ route('reseaux.create') }}" style="color:var(--primary)">Créer le premier →</a>
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($reseaux->hasPages())
    <div class="card-footer bg-white border-top">{{ $reseaux->links() }}</div>
    @endif
</div>
@endsection
