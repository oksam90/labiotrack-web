@extends("layouts.app")
@section("title","Contenants")
@section("content")
<div class="page-header"><h4 class="fw-bold mb-0">Types de contenants</h4></div>
<div class="row g-3">
<div class="col-md-5"><div class="card"><div class="card-header">Ajouter</div><div class="card-body">
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
<thead><tr><th>Contenant</th><th>Code</th><th>Poids moy.</th><th>Coût</th></tr></thead>
<tbody>
@foreach($contenants as $c)
<tr><td><strong>{{ $c->nom }}</strong></td><td><code>{{ $c->code }}</code></td><td>{{ $c->poids_moyen_kg }} kg</td><td>{{ number_format($c->cout_unitaire) }} FCFA</td></tr>
@endforeach
</tbody></table></div>
<div class="card-footer">{{ $contenants->links() }}</div>
</div></div>
</div>
@endsection