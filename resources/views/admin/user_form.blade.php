@extends("layouts.app")
@section("title", $user ? "Modifier utilisateur" : "Nouvel utilisateur")
@section("content")
@php
    $current = auth()->user();
    // Rôles attribuables selon l'utilisateur courant
    $rolesDisponibles = $current->isSuperAdmin()
        ? ["superadmin","admin","admin_reseau","qhse","agent","collecteur","prestataire","client_signataire"]
        : ($current->isAdminReseau()
            ? ["admin","qhse","agent","client_signataire"] // AdminRéseau ne peut pas créer un autre AdminRéseau
            : ["qhse","agent","client_signataire"]); // admin local
    $rolesLabels = [
        'superadmin'        => 'SuperAdmin',
        'admin'             => 'Admin (établissement)',
        'admin_reseau'      => 'AdminRéseau',
        'qhse'              => 'QHSE',
        'agent'             => 'Agent',
        'collecteur'        => 'Collecteur',
        'prestataire'       => 'Prestataire',
        'client_signataire' => 'Client signataire',
    ];
@endphp

<div class="page-header"><h4>{{ $user ? "Modifier" : "Créer" }} un utilisateur</h4></div>
<div class="row justify-content-center"><div class="col-md-8">
<div class="card"><div class="card-body p-4">

@if($errors->any())
<div class="alert alert-danger">
    <strong>Erreurs :</strong>
    <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $user ? route("admin.utilisateurs.update", $user->id) : route("admin.utilisateurs.store") }}">
@csrf @if($user) @method("PUT") @endif

<div class="row g-3">
<div class="col-md-6"><label class="form-label">Prénom *</label><input type="text" name="prenom" class="form-control" value="{{ $user->prenom ?? old("prenom") }}" required></div>
<div class="col-md-6"><label class="form-label">Nom *</label><input type="text" name="nom" class="form-control" value="{{ $user->nom ?? old("nom") }}" required></div>
<div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" value="{{ $user->email ?? old("email") }}" required></div>
<div class="col-md-6"><label class="form-label">Téléphone</label><input type="text" name="telephone" class="form-control" value="{{ $user->telephone ?? "" }}"></div>

<div class="col-md-6"><label class="form-label">Mot de passe {{ $user ? "(laisser vide pour ne pas changer)" : "*" }}</label><input type="password" name="password" class="form-control" minlength="8" {{ $user ? "" : "required" }}></div>
<div class="col-md-6"><label class="form-label">Confirmer le mot de passe {{ $user ? "" : "*" }}</label><input type="password" name="password_confirmation" class="form-control" minlength="8" {{ $user ? "" : "required" }}></div>

<div class="col-md-4"><label class="form-label">Rôle *</label>
<select name="role" class="form-select" required>
@foreach($rolesDisponibles as $r)
<option value="{{ $r }}" {{ ($user->role ?? "") === $r ? "selected" : "" }}>{{ $rolesLabels[$r] ?? ucfirst($r) }}</option>
@endforeach
</select>
</div>

@if(isset($reseaux) && $reseaux->count() > 0)
<div class="col-md-4"><label class="form-label">Réseau
@if($current->isAdminReseau()) <small class="text-muted">(votre réseau)</small>@endif
</label>
<select name="reseau_id" class="form-select" {{ $current->isAdminReseau() ? 'disabled' : '' }}>
<option value="">— Aucun —</option>
@foreach($reseaux as $r)
<option value="{{ $r->id }}" {{ ($user->reseau_id ?? ($current->isAdminReseau() ? $current->reseau_id : null)) == $r->id ? "selected" : "" }}>{{ $r->nom }}</option>
@endforeach
</select>
@if($current->isAdminReseau())
<input type="hidden" name="reseau_id" value="{{ $current->reseau_id }}">
@endif
</div>
@endif

<div class="col-md-4"><label class="form-label">Établissement</label>
<select name="etablissement_id" class="form-select">
<option value="">— Aucun —</option>
@foreach($etablissements as $e)
<option value="{{ $e->id }}" {{ ($user->etablissement_id ?? "") == $e->id ? "selected" : "" }}>{{ $e->nom }}</option>
@endforeach
</select>
</div>
</div>

<div class="d-flex gap-2 mt-4">
<button type="submit" class="btn btn-primary">Enregistrer</button>
<a href="{{ route("admin.utilisateurs.index") }}" class="btn btn-outline-secondary">Annuler</a>
</div>
</form>
</div></div></div></div>
@endsection
