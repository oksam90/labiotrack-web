@extends('layouts.app')
@section('title','Toutes les structures')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-buildings me-2 text-success"></i>Toutes les structures</h4>
        <small class="text-muted">{{ $etablissements->total() }} structures enregistrées</small>
    </div>
    @can('create', \App\Models\Etablissement::class)
    <a href="{{ route('admin.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus me-1"></i>Nouvelle structure</a>
    @endcan
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Structure</th><th>Type</th><th>Ville</th><th>Lits</th>
                    <th class="text-center">Utilisateurs</th><th class="text-center">Services</th>
                    <th class="text-center">Déclarations</th><th>Statut</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($etablissements as $e)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $e->nom }}</div>
                        <small class="text-muted">{{ $e->email ?? '—' }}</small>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ ucfirst($e->type) }}</span></td>
                    <td>{{ $e->ville ?? '—' }}</td>
                    <td>{{ $e->nombre_lits }}</td>
                    <td class="text-center">{{ $e->users_count }}</td>
                    <td class="text-center">{{ $e->services_count }}</td>
                    <td class="text-center">{{ $e->declarations_count }}</td>
                    <td>
                        <span class="badge {{ $e->actif ? 'bg-success' : 'bg-secondary' }}">
                            {{ $e->actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('superadmin.etablissement', $e->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:.72rem;padding:.15rem .5rem;"><i class="bi bi-eye"></i></a>
                            <form method="POST" action="{{ route('superadmin.switch-tenant', $e->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" style="font-size:.72rem;padding:.15rem .5rem;" title="Basculer"><i class="bi bi-arrow-right-circle"></i></button>
                            </form>
                            @can('update', $e)
                            <a href="{{ route('admin.edit', $e->id) }}" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;padding:.15rem .5rem;"><i class="bi bi-pencil"></i></a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $etablissements->links() }}</div>
</div>
@endsection
