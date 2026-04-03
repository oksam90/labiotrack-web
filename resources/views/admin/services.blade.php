@extends("layouts.app")
@section("title","Services")
@section("content")
<div class="page-header"><h4 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2"></i>Services</h4></div>
<div class="row g-3">
<div class="col-md-5"><div class="card"><div class="card-header">Ajouter un service</div><div class="card-body">
<form method="POST" action="{{ route("admin.services.store") }}">@csrf
@if($errors->any())
<div class="alert alert-danger py-2 mb-2">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif
<div class="mb-2"><label class="form-label">Nom *</label><input type="text" name="nom" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
<div class="mb-2"><label class="form-label">Responsable</label><input type="text" name="responsable" class="form-control"></div>
@if(Auth::user()->isGlobal())
<div class="mb-3"><label class="form-label">Établissement *</label>
<select name="etablissement_id" class="form-select" required>
<option value="">— Sélectionner —</option>
@foreach($etablissements as $e)<option value="{{ $e->id }}">{{ $e->nom }}</option>@endforeach
</select></div>
@else
<input type="hidden" name="etablissement_id" value="{{ Auth::user()->etablissement_id }}">
@endif
<button type="submit" class="btn btn-primary btn-sm">Ajouter</button>
</form></div></div></div>
<div class="col-md-7"><div class="card"><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Service</th><th>Responsable</th><th>Statut</th><th></th></tr></thead>
<tbody>
@foreach($services as $s)
<tr>
<td><strong>{{ $s->nom }}</strong><br><small class="text-muted">{{ $s->description }}</small></td>
<td>{{ $s->responsable ?? "—" }}</td>
<td>@if($s->actif)<span class="badge bg-success">Actif</span>@else<span class="badge bg-secondary">Inactif</span>@endif</td>
<td>
<div class="d-flex gap-1">
<button type="button" class="btn btn-sm btn-outline-secondary py-0" title="Modifier"
    data-bs-toggle="modal" data-bs-target="#editModal{{ $s->id }}"><i class="bi bi-pencil"></i></button>
<form method="POST" action="{{ route('admin.services.toggle', $s->id) }}" class="d-inline">@csrf
<button type="submit" class="btn btn-sm {{ $s->actif ? 'btn-outline-warning' : 'btn-outline-success' }} py-0"
    title="{{ $s->actif ? 'Désactiver' : 'Activer' }}"><i class="bi {{ $s->actif ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i></button></form>
<form method="POST" action="{{ route('admin.services.destroy', $s->id) }}" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce service ?')">@csrf @method("DELETE")
<button type="submit" class="btn btn-sm btn-outline-danger py-0" title="Supprimer"><i class="bi bi-trash"></i></button></form>
</div>
</td>
</tr>
@endforeach
</tbody></table></div>
<div class="card-footer">{{ $services->links() }}</div>
</div></div>
</div>

{{-- Modales de modification --}}
@foreach($services as $s)
<div class="modal fade" id="editModal{{ $s->id }}" tabindex="-1" aria-labelledby="editLabel{{ $s->id }}" aria-hidden="true">
<div class="modal-dialog"><div class="modal-content">
<form method="POST" action="{{ route('admin.services.update', $s->id) }}">@csrf @method("PUT")
<div class="modal-header">
    <h5 class="modal-title" id="editLabel{{ $s->id }}"><i class="bi bi-pencil me-2"></i>Modifier le service</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="mb-3"><label class="form-label">Nom *</label><input type="text" name="nom" class="form-control" value="{{ $s->nom }}" required></div>
    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2">{{ $s->description }}</textarea></div>
    <div class="mb-3"><label class="form-label">Responsable</label><input type="text" name="responsable" class="form-control" value="{{ $s->responsable }}"></div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
    <button type="submit" class="btn btn-primary">Enregistrer</button>
</div>
</form>
</div></div></div>
@endforeach
@endsection
