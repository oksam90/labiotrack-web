@extends('layouts.app')
@section('title','Établissements')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-building me-2"></i>Établissements</h4>
    <a href="{{ route('admin.create') }}" class="btn btn-primary"><i class="bi bi-plus me-1"></i>Ajouter</a>
</div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Nom</th><th>Type</th><th>Ville</th><th>QHSE</th><th>Lits</th><th>Statut</th><th></th></tr></thead>
<tbody>
@foreach($etablissements as $e)
<tr>
<td><strong>{{ $e->nom }}</strong></td>
<td><span class="badge bg-info">{{ ucfirst($e->type) }}</span></td>
<td>{{ $e->ville }}</td>
<td>{{ $e->responsable_qhse }}</td>
<td>{{ $e->nombre_lits }}</td>
<td>@if($e->actif)<span class="badge bg-success">Actif</span>@else<span class="badge bg-secondary">Inactif</span>@endif</td>
<td class="d-flex gap-1">
<a href="{{ route('admin.edit', $e->id) }}" class="btn btn-sm btn-outline-secondary py-0" title="Modifier"><i class="bi bi-pencil"></i></a>
@if(auth()->user()->isSuperAdmin())
<form method="POST" action="{{ route('admin.toggle', $e->id) }}" class="d-inline">@csrf
<button type="submit" class="btn btn-sm {{ $e->actif ? 'btn-outline-warning' : 'btn-outline-success' }} py-0" title="{{ $e->actif ? 'Désactiver' : 'Activer' }}"><i class="bi {{ $e->actif ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i></button></form>
<form method="POST" action="{{ route('admin.destroy', $e->id) }}" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet établissement ?')">@csrf @method('DELETE')
<button type="submit" class="btn btn-sm btn-outline-danger py-0" title="Supprimer"><i class="bi bi-trash"></i></button></form>
@endif
</td>
</tr>
@endforeach
</tbody>
</table></div><div class="card-footer">{{ $etablissements->links() }}</div></div>
@endsection
