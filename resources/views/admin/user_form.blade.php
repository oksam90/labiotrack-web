@extends("layouts.app")
@section("title", $user ? __('admin.page_user_edit_title') : __('admin.page_user_new_title'))
@section("content")
@php
    $current = auth()->user();
    // Rôles attribuables selon l'utilisateur courant
    $rolesDisponibles = $current->isSuperAdmin()
        ? ["superadmin","admin","admin_reseau","qhse","agent","collecteur","prestataire","client_signataire"]
        : ($current->isAdminReseau()
            ? ["admin","qhse","agent","client_signataire"] // AdminRéseau ne peut pas créer un autre AdminRéseau
            : ["qhse","agent","client_signataire"]); // admin local
@endphp

<div class="page-header"><h4>{{ $user ? __('admin.header_user_form_edit') : __('admin.header_user_form_new') }}</h4></div>
<div class="row justify-content-center"><div class="col-md-8">
<div class="card"><div class="card-body p-4">

@if($errors->any())
<div class="alert alert-danger">
    <strong>{{ __('admin.errors_title') }}</strong>
    <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $user ? route("admin.utilisateurs.update", $user->id) : route("admin.utilisateurs.store") }}">
@csrf @if($user) @method("PUT") @endif

<div class="row g-3">
<div class="col-md-6"><label class="form-label">{{ __('admin.col_firstname') }} *</label><input type="text" name="prenom" class="form-control" value="{{ $user->prenom ?? old("prenom") }}" required></div>
<div class="col-md-6"><label class="form-label">{{ __('admin.col_name') }} *</label><input type="text" name="nom" class="form-control" value="{{ $user->nom ?? old("nom") }}" required></div>
<div class="col-md-6"><label class="form-label">{{ __('admin.col_email') }} *</label><input type="email" name="email" class="form-control" value="{{ $user->email ?? old("email") }}" required></div>
<div class="col-md-6"><label class="form-label">{{ __('admin.col_phone') }}</label><input type="text" name="telephone" class="form-control" value="{{ $user->telephone ?? "" }}"></div>

<div class="col-md-6"><label class="form-label">{{ __('admin.col_password') }} {{ $user ? __('admin.col_password_hint') : "*" }}</label><input type="password" name="password" class="form-control" minlength="8" {{ $user ? "" : "required" }}></div>
<div class="col-md-6"><label class="form-label">{{ __('admin.col_password_confirm') }} {{ $user ? "" : "*" }}</label><input type="password" name="password_confirmation" class="form-control" minlength="8" {{ $user ? "" : "required" }}></div>

<div class="col-md-4"><label class="form-label">{{ __('admin.col_role') }} *</label>
<select name="role" class="form-select" required>
@foreach($rolesDisponibles as $r)
<option value="{{ $r }}" {{ ($user->role ?? "") === $r ? "selected" : "" }}>{{ __('admin.role_' . $r) ?? ucfirst($r) }}</option>
@endforeach
</select>
</div>

@if(isset($reseaux) && $reseaux->count() > 0)
<div class="col-md-4"><label class="form-label">{{ __('admin.col_reseau') }}
@if($current->isAdminReseau()) <small class="text-muted">{{ __('admin.col_reseau_hint_my') }}</small>@endif
</label>
<select name="reseau_id" class="form-select" {{ $current->isAdminReseau() ? 'disabled' : '' }}>
<option value="">{{ __('admin.select_none') }}</option>
@foreach($reseaux as $r)
<option value="{{ $r->id }}" {{ ($user->reseau_id ?? ($current->isAdminReseau() ? $current->reseau_id : null)) == $r->id ? "selected" : "" }}>{{ $r->nom }}</option>
@endforeach
</select>
@if($current->isAdminReseau())
<input type="hidden" name="reseau_id" value="{{ $current->reseau_id }}">
@endif
</div>
@endif

<div class="col-md-4"><label class="form-label">{{ __('admin.col_etab') }}</label>
<select name="etablissement_id" class="form-select">
<option value="">{{ __('admin.select_none') }}</option>
@foreach($etablissements as $e)
<option value="{{ $e->id }}" {{ ($user->etablissement_id ?? "") == $e->id ? "selected" : "" }}>{{ $e->nom }}</option>
@endforeach
</select>
</div>
</div>

<div class="d-flex gap-2 mt-4">
<button type="submit" class="btn btn-primary">{{ __('admin.btn_save') }}</button>
<a href="{{ route("admin.utilisateurs.index") }}" class="btn btn-outline-secondary">{{ __('admin.btn_cancel') }}</a>
</div>
</form>
</div></div></div></div>
@endsection
