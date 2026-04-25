@extends('layouts.app')
@section('title', $reseau->exists ? 'Modifier le réseau' : 'Nouveau réseau')

@section('content')
<div class="topbar">
    <span class="topbar-title">
        <i class="bi bi-diagram-3 me-2"></i>
        {{ $reseau->exists ? 'Modifier le réseau' : 'Nouveau réseau' }}
    </span>
    <a href="{{ route('reseaux.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Retour</a>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <strong>Erreurs :</strong>
    <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $reseau->exists ? route('reseaux.update', $reseau->id) : route('reseaux.store') }}"
      class="card border-0 shadow-sm p-4">
    @csrf
    @if($reseau->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nom du réseau *</label>
            <input type="text" name="nom" value="{{ old('nom', $reseau->nom) }}" class="form-control" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Email contact</label>
            <input type="email" name="contact_email" value="{{ old('contact_email', $reseau->contact_email) }}" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">Téléphone contact</label>
            <input type="text" name="contact_telephone" value="{{ old('contact_telephone', $reseau->contact_telephone) }}" class="form-control">
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-control">{{ old('description', $reseau->description) }}</textarea>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="hidden" name="actif" value="0">
                <input type="checkbox" name="actif" value="1" id="actif" class="form-check-input"
                    {{ old('actif', $reseau->actif ?? true) ? 'checked' : '' }}>
                <label for="actif" class="form-check-label">Réseau actif</label>
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <a href="{{ route('reseaux.index') }}" class="btn btn-light">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>{{ $reseau->exists ? 'Mettre à jour' : 'Enregistrer' }}
        </button>
    </div>
</form>
@endsection
