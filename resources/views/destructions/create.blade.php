@extends('layouts.app')
@section('title', __('destructions.page_create_title'))
@section('content')

<div class="page-header">
    <h4 class="fw-bold mb-0"><i class="bi bi-fire me-2 text-danger"></i>{{ __('destructions.header_create') }}</h4>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-body p-4">

                {{-- Info collecte --}}
                <div class="alert alert-info mb-4">
                    <strong>{{ __('destructions.form_bordereau_label') }}</strong> {{ $collecte->numero_bordereau }}
                    &nbsp;|&nbsp;
                    <strong>{{ __('destructions.form_containers_label') }}</strong> {{ $collecte->nombre_contenants }}
                    &nbsp;|&nbsp;
                    <strong>{{ __('destructions.form_weight_declared') }}</strong> {{ number_format($collecte->poids_declare_kg, 1) }} kg
                </div>

                <form method="POST" action="{{ route('destructions.store') }}">
                    @csrf
                    <input type="hidden" name="collecte_id" value="{{ $collecte->id }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('destructions.form_weight_real') }} <span class="text-danger">*</span></label>
                            <input type="number" name="poids_reel_kg" class="form-control @error('poids_reel_kg') is-invalid @enderror"
                                   step="0.01" min="0.01" value="{{ old('poids_reel_kg') }}" required>
                            @error('poids_reel_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('destructions.form_method') }} <span class="text-danger">*</span></label>
                            <select name="methode" class="form-select @error('methode') is-invalid @enderror" required>
                                <option value="">{{ __('destructions.form_select_placeholder') }}</option>
                                <option value="incineration"          {{ old('methode')=='incineration'          ? 'selected' : '' }}>{{ __('destructions.method_incineration') }}</option>
                                <option value="autoclave"             {{ old('methode')=='autoclave'             ? 'selected' : '' }}>{{ __('destructions.method_autoclave') }}</option>
                                <option value="desinfection_chimique" {{ old('methode')=='desinfection_chimique' ? 'selected' : '' }}>{{ __('destructions.method_desinfection') }}</option>
                                <option value="autre"                 {{ old('methode')=='autre'                 ? 'selected' : '' }}>{{ __('destructions.method_other') }}</option>
                            </select>
                            @error('methode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('destructions.form_site') }}</label>
                            <input type="text" name="site_traitement" class="form-control"
                                   placeholder="{{ __('destructions.form_site_ph') }}" value="{{ old('site_traitement') }}" maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('destructions.form_date_reception') }}</label>
                            <input type="date" name="date_reception" class="form-control" value="{{ old('date_reception') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('destructions.form_date_destruction') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date_destruction" class="form-control @error('date_destruction') is-invalid @enderror"
                                   value="{{ old('date_destruction', date('Y-m-d')) }}" required>
                            @error('date_destruction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('destructions.form_conform') }}</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="conforme" id="conforme" class="form-check-input" value="1" {{ old('conforme', '1') ? 'checked' : '' }}>
                                <label for="conforme" class="form-check-label">{{ __('destructions.form_conform_norms') }}</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ __('destructions.form_notes') }}</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="{{ __('destructions.form_notes_ph') }}" maxlength="500">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-check-circle me-2"></i>{{ __('destructions.btn_save_and_cert') }}
                        </button>
                        <a href="{{ route('collectes.show', $collecte->id) }}" class="btn btn-light">{{ __('common.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
