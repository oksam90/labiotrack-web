@extends('layouts.app')
@section('title','Nouvelle Collecte')
@section('content')
<div class="page-header"><h4 class="fw-bold mb-0"><i class="bi bi-truck me-2 text-primary"></i>Créer une collecte</h4></div>
<div class="row justify-content-center"><div class="col-md-8">
<div class="card"><div class="card-body p-4">
<form method="POST" action="{{ route('collectes.store') }}" enctype="multipart/form-data">@csrf
<div class="mb-4">
    <label class="form-label fw-bold">Déclarations disponibles en stock</label>
    <small class="text-muted d-block mb-2">Cochez les déclarations à inclure dans cette collecte</small>
    <div class="table-responsive">
    <table class="table table-sm">
    <thead><tr><th></th><th>Service</th><th>Contenant</th><th>Qté</th><th>Poids est.</th><th>Date</th></tr></thead>
    <tbody>
    @foreach($declarationsDisponibles as $d)
    <tr>
        <td><input type="checkbox" name="declarations[]" value="{{ $d->id }}" class="form-check-input"></td>
        <td>{{ $d->service_nom }}</td>
        <td><small>{{ $d->contenant_nom }}</small></td>
        <td><strong>{{ $d->nombre_contenants }}</strong></td>
        <td>{{ number_format($d->poids_estime_kg,1) }} kg</td>
        <td><small>{{ \Carbon\Carbon::parse($d->date_declaration)->format('d/m/Y') }}</small></td>
    </tr>
    @endforeach
    @if($declarationsDisponibles->isEmpty())
    <tr><td colspan="6" class="text-center text-muted py-3">Aucune déclaration en stock disponible</td></tr>
    @endif
    </tbody>
    </table>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">Collecteur</label>
        <select name="collecteur_id" class="form-select">
            <option value="">— Sélectionner —</option>
            @foreach($collecteurs as $c)<option value="{{ $c->id }}">{{ $c->prenom }} {{ $c->nom }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Véhicule</label>
        <input type="text" name="vehicule" class="form-control" placeholder="Ex: DK-1234-AB">
    </div>
</div>
<div class="mb-3"><label class="form-label">Photo des déchets chargés (optionnel)</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
<div class="mb-4"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
<div class="d-flex gap-2">
<button type="submit" class="btn btn-primary flex-fill py-2"><i class="bi bi-truck me-2"></i>Créer la collecte & générer le bordereau</button>
<a href="{{ route('collectes.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>
</form></div></div>
</div></div>
@endsection
