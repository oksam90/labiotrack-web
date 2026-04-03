@extends("layouts.app")
@section("title","Contenants")
@section("content")
<div class="page-header"><h4 class="fw-bold mb-0"><i class="bi bi-box me-2"></i>Types de contenants</h4></div>
<div class="row g-3">
<div class="col-md-5"><div class="card"><div class="card-header">Ajouter un contenant</div><div class="card-body">
<form method="POST" action="{{ route("admin.contenants.store") }}">@csrf
@if($errors->any())
<div class="alert alert-danger py-2 mb-2">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif
<div class="mb-2"><label class="form-label">Nom *</label><input type="text" name="nom" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Code *</label><input type="text" name="code" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Type déchet *</label><select name="type_dechet_id" class="form-select" required><option value="">— Sélectionner —</option>
@foreach($typeDechets as $td)<option value="{{ $td->id }}">{{ $td->nom }}</option>@endforeach</select></div>
<div class="row g-2 mb-3">
<div class="col-6"><label class="form-label">Poids moy. (kg)*</label><input type="number" step="0.01" name="poids_moyen_kg" class="form-control" required></div>
<div class="col-6"><label class="form-label">Coût (FCFA)</label><input type="number" step="0.01" name="cout_unitaire" class="form-control" value="0"></div>
</div>
<button type="submit" class="btn btn-primary btn-sm">Ajouter</button>
</form></div></div></div>
<div class="col-md-7"><div class="card"><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Contenant</th><th>Code</th><th>Poids moy.</th><th>Coût</th><th></th></tr></thead>
<tbody>
@foreach($contenants as $c)
<tr>
<td><strong>{{ $c->nom }}</strong></td>
<td><code>{{ $c->code }}</code></td>
<td>{{ $c->poids_moyen_kg }} kg</td>
<td>{{ number_format($c->cout_unitaire) }} FCFA</td>
<td>
<div class="d-flex gap-1">
<button type="button" class="btn btn-sm btn-outline-secondary py-0" title="Modifier"
    data-bs-toggle="modal" data-bs-target="#editContenant{{ $c->id }}"><i class="bi bi-pencil"></i></button>
<form method="POST" action="{{ route('admin.contenants.destroy', $c->id) }}" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce contenant ?')">@csrf @method('DELETE')
<button type="submit" class="btn btn-sm btn-outline-danger py-0" title="Supprimer"><i class="bi bi-trash"></i></button></form>
</div>
</td>
</tr>
@endforeach
</tbody></table></div>
<div class="card-footer">{{ $contenants->links() }}</div>
</div></div>
</div>

{{-- Modales de modification --}}
@foreach($contenants as $c)
<div class="modal fade" id="editContenant{{ $c->id }}" tabindex="-1" aria-labelledby="editLabel{{ $c->id }}" aria-hidden="true">
<div class="modal-dialog"><div class="modal-content">
<form method="POST" action="{{ route('admin.contenants.update', $c->id) }}">@csrf @method("PUT")
<div class="modal-header">
    <h5 class="modal-title" id="editLabel{{ $c->id }}"><i class="bi bi-pencil me-2"></i>Modifier le contenant</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="mb-3"><label class="form-label">Nom *</label><input type="text" name="nom" class="form-control" value="{{ $c->nom }}" required></div>
    <div class="row g-2 mb-3">
        <div class="col-6"><label class="form-label">Poids moy. (kg) *</label><input type="number" step="0.01" name="poids_moyen_kg" class="form-control" value="{{ $c->poids_moyen_kg }}" required></div>
        <div class="col-6"><label class="form-label">Coût (FCFA)</label><input type="number" step="0.01" name="cout_unitaire" class="form-control" value="{{ $c->cout_unitaire }}"></div>
    </div>
    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2">{{ $c->description ?? '' }}</textarea></div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
    <button type="submit" class="btn btn-primary">Enregistrer</button>
</div>
</form>
</div></div></div>
@endforeach
@endsection
