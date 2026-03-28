@extends('layouts.app')
@section('title', $etablissement ? 'Modifier établissement' : 'Nouvel établissement')
@section('content')
<div class="page-header"><h4>{{ $etablissement ? 'Modifier' : 'Ajouter' }} un établissement</h4></div>
<div class="row justify-content-center"><div class="col-md-7">
<div class="card"><div class="card-body p-4">
<form method="POST" action="{{ $etablissement ? route('admin.update', $etablissement->id) : route('admin.store') }}">
@csrf @if($etablissement) @method('PUT') @endif
<div class="row g-3">
<div class="col-md-8"><label class="form-label">Nom *</label><input type="text" name="nom" class="form-control" value="{{ $etablissement->nom ?? '' }}" required></div>
<div class="col-md-4"><label class="form-label">Type *</label>
<select name="type" class="form-select" required>
@foreach(['clinique','hopital','cabinet','laboratoire'] as $t)
<option value="{{ $t }}" {{ ($etablissement->type ?? '') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
@endforeach
</select></div>
<div class="col-12"><label class="form-label">Adresse *</label><textarea name="adresse" class="form-control" required>{{ $etablissement->adresse ?? '' }}</textarea></div>
<div class="col-md-6"><label class="form-label">Ville</label><input type="text" name="ville" class="form-control" value="{{ $etablissement->ville ?? '' }}"></div>
<div class="col-md-6"><label class="form-label">Téléphone</label><input type="text" name="telephone" class="form-control" value="{{ $etablissement->telephone ?? '' }}"></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ $etablissement->email ?? '' }}"></div>
<div class="col-md-6"><label class="form-label">Responsable QHSE</label><input type="text" name="responsable_qhse" class="form-control" value="{{ $etablissement->responsable_qhse ?? '' }}"></div>
<div class="col-md-4"><label class="form-label">Nombre de lits</label><input type="number" name="nombre_lits" class="form-control" value="{{ $etablissement->nombre_lits ?? 0 }}"></div>
</div>
<div class="d-flex gap-2 mt-4">
<button type="submit" class="btn btn-primary">Enregistrer</button>
<a href="{{ route('admin.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>
</form></div></div></div></div>
@endsection
