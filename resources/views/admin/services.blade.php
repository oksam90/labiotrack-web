@extends("layouts.app")
@section("title","Services")
@section("content")
<div class="page-header"><h4 class="fw-bold mb-0">Services</h4></div>
<div class="row g-3">
<div class="col-md-5"><div class="card"><div class="card-header">Ajouter</div><div class="card-body">
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
<td><form method="POST" action="{{ route("admin.services.destroy", $s->id) }}">@csrf @method("DELETE")
<button type="submit" class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-trash"></i></button></form></td>
</tr>
@endforeach
</tbody></table></div>
<div class="card-footer">{{ $services->links() }}</div>
</div></div>
</div>
@endsection