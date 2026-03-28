@extends('layouts.app')
@section('title', 'Enregistrer destruction')
@section('content')

<div class="page-header">
    <h4 class="fw-bold mb-0"><i class="bi bi-fire me-2 text-danger"></i>Enregistrer une destruction</h4>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-body p-4">

                {{-- Info collecte --}}
                <div class="alert alert-info mb-4">
                    <strong>Bordereau :</strong> {{ $collecte->numero_bordereau }}
                    &nbsp;|&nbsp;
                    <strong>Contenants :</strong> {{ $collecte->nombre_contenants }}
                    &nbsp;|&nbsp;
                    <strong>Poids déclaré :</strong> {{ number_format($collecte->poids_declare_kg, 1) }} kg
                </div>

                <form method="POST" action="{{ route('destructions.store') }}">
                    @csrf
                    <input type="hidden" name="collecte_id" value="{{ $collecte->id }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Poids réel (kg) <span class="text-danger">*</span></label>
                            <input type="number" name="poids_reel_kg" class="form-control @error('poids_reel_kg') is-invalid @enderror"
                                   step="0.01" min="0.01" value="{{ old('poids_reel_kg') }}" required>
                            @error('poids_reel_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Méthode <span class="text-danger">*</span></label>
                            <select name="methode" class="form-select @error('methode') is-invalid @enderror" required>
                                <option value="">— Sélectionner —</option>
                                <option value="incineration"          {{ old('methode')=='incineration'          ? 'selected' : '' }}>🔥 Incinération</option>
                                <option value="autoclave"             {{ old('methode')=='autoclave'             ? 'selected' : '' }}>♨️ Autoclave</option>
                                <option value="desinfection_chimique" {{ old('methode')=='desinfection_chimique' ? 'selected' : '' }}>🧪 Désinfection chimique</option>
                                <option value="autre"                 {{ old('methode')=='autre'                 ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('methode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Site de traitement</label>
                            <input type="text" name="site_traitement" class="form-control"
                                   placeholder="Nom du site…" value="{{ old('site_traitement') }}" maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date de réception</label>
                            <input type="date" name="date_reception" class="form-control" value="{{ old('date_reception') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date de destruction <span class="text-danger">*</span></label>
                            <input type="date" name="date_destruction" class="form-control @error('date_destruction') is-invalid @enderror"
                                   value="{{ old('date_destruction', date('Y-m-d')) }}" required>
                            @error('date_destruction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Conforme ?</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="conforme" id="conforme" class="form-check-input" value="1" {{ old('conforme', '1') ? 'checked' : '' }}>
                                <label for="conforme" class="form-check-label">Destruction conforme aux normes</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes / Observations</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Remarques éventuelles…" maxlength="500">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-check-circle me-2"></i>Enregistrer & Générer le certificat
                        </button>
                        <a href="{{ route('collectes.show', $collecte->id) }}" class="btn btn-light">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
