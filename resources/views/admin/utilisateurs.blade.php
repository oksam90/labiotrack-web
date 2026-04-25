@extends('layouts.app')
@section('title','Utilisateurs')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>Utilisateurs</h4>
    <a href="{{ route('admin.utilisateurs.create') }}" class="btn btn-primary"><i class="bi bi-plus me-1"></i>Ajouter</a>
</div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Réseau</th><th>Établissement</th><th>Statut</th><th></th></tr></thead>
<tbody>
@foreach($users as $u)
<tr>
<td>{{ $u->prenom }} {{ $u->nom }}</td>
<td><small>{{ $u->email }}</small></td>
<td><span class="role-badge role-{{ $u->role }}">{{ str_replace('_', ' ', ucfirst($u->role)) }}</span></td>
<td><small class="text-muted">{{ $u->reseau_nom ?? '—' }}</small></td>
<td><small>{{ $u->etablissement_nom ?? '—' }}</small></td>
<td>@if($u->actif)<span class="badge bg-success">Actif</span>@else<span class="badge bg-secondary">Inactif</span>@endif</td>
<td class="d-flex gap-1">
<a href="{{ route('admin.utilisateurs.edit', $u->id) }}" class="btn btn-sm btn-outline-secondary py-0" title="Modifier"><i class="bi bi-pencil"></i></a>
<form method="POST" action="{{ route('admin.utilisateurs.toggle', $u->id) }}" class="d-inline">@csrf
<button type="submit" class="btn btn-sm {{ $u->actif ? 'btn-outline-warning' : 'btn-outline-success' }} py-0" title="{{ $u->actif ? 'Désactiver' : 'Activer' }}"><i class="bi {{ $u->actif ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i></button></form>
@if($u->id !== auth()->id())
<form method="POST" action="{{ route('admin.utilisateurs.destroy', $u->id) }}" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">@csrf @method('DELETE')
<button type="submit" class="btn btn-sm btn-outline-danger py-0" title="Supprimer"><i class="bi bi-trash"></i></button></form>
@endif
</td>
</tr>
@endforeach
</tbody>
</table></div><div class="card-footer">{{ $users->links() }}</div></div>
@endsection
