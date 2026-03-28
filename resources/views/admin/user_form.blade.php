@extends("layouts.app")
@section("title", $user ? "Modifier utilisateur" : "Nouvel utilisateur")
@section("content")
<div class="page-header"><h4>{{ $user ? "Modifier" : "Créer" }} un utilisateur</h4></div>
<div class="row justify-content-center"><div class="col-md-7">
<div class="card"><div class="card-body p-4">
<form method="POST" action="{{ $user ? route("admin.utilisateurs.update", $user->id) : route("admin.utilisateurs.store") }}">
@csrf @if($user) @method("PUT") @endif
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Prénom *</label><input type="text" name="prenom" class="form-control" value="{{ $user->prenom ?? old("prenom") }}" required></div>
<div class="col-md-6"><label class="form-label">Nom *</label><input type="text" name="nom" class="form-control" value="{{ $user->nom ?? old("nom") }}" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ $user->email ?? old("email") }}"></div>
<div class="col-md-6"><label class="form-label">Mot de passe {{ $user ? "(laisser vide)" : "*" }}</label><input type="password" name="password" class="form-control" minlength="8"></div>
<div class="col-md-6"><label class="form-label">Rôle *</label>
<select name="role" class="form-select" required>
@foreach(["superadmin","admin","qhse","agent","collecteur","prestataire"] as $r)
<option value="{{ $r }}" {{ ($user->role ?? "") === $r ? "selected" : "" }}>{{ ucfirst($r) }}</option>
@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Établissement</label>
<select name="etablissement_id" class="form-select">
<option value="">— Aucun —</option>
@foreach($etablissements as $e)
<option value="{{ $e->id }}" {{ ($user->etablissement_id ?? "") == $e->id ? "selected" : "" }}>{{ $e->nom }}</option>
@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Téléphone</label><input type="text" name="telephone" class="form-control" value="{{ $user->telephone ?? "" }}"></div>
</div>
<div class="d-flex gap-2 mt-4">
<button type="submit" class="btn btn-primary">Enregistrer</button>
<a href="{{ route("admin.utilisateurs") }}" class="btn btn-outline-secondary">Annuler</a>
</div></form></div></div></div></div>
@endsection