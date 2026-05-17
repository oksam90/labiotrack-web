@extends('layouts.app')
@section('title', $reseau->exists ? __('reseaux.page_edit_title') : __('reseaux.page_create_title'))

@section('content')
<div class="topbar">
    <span class="topbar-title">
        <i class="bi bi-diagram-3 me-2"></i>
        {{ $reseau->exists ? __('reseaux.page_edit_title') : __('reseaux.page_create_title') }}
    </span>
    <a href="{{ route('reseaux.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>{{ __('reseaux.btn_back') }}</a>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <strong>{{ __('reseaux.errors_title') }}</strong>
    <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $reseau->exists ? route('reseaux.update', $reseau->id) : route('reseaux.store') }}"
      class="card border-0 shadow-sm p-4">
    @csrf
    @if($reseau->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">{{ __('reseaux.form_name') }} *</label>
            <input type="text" name="nom" value="{{ old('nom', $reseau->nom) }}" class="form-control" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">{{ __('reseaux.form_email') }}</label>
            <input type="email" name="contact_email" value="{{ old('contact_email', $reseau->contact_email) }}" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">{{ __('reseaux.form_phone') }}</label>
            <input type="text" name="contact_telephone" value="{{ old('contact_telephone', $reseau->contact_telephone) }}" class="form-control">
        </div>

        <div class="col-12">
            <label class="form-label">{{ __('reseaux.form_description') }}</label>
            <textarea name="description" rows="3" class="form-control">{{ old('description', $reseau->description) }}</textarea>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="hidden" name="actif" value="0">
                <input type="checkbox" name="actif" value="1" id="actif" class="form-check-input"
                    {{ old('actif', $reseau->actif ?? true) ? 'checked' : '' }}>
                <label for="actif" class="form-check-label">{{ __('reseaux.form_active') }}</label>
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <a href="{{ route('reseaux.index') }}" class="btn btn-light">{{ __('reseaux.btn_cancel') }}</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>{{ $reseau->exists ? __('reseaux.btn_update') : __('reseaux.btn_save') }}
        </button>
    </div>
</form>
@endsection
