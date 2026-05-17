@extends('layouts.app')
@section('title', __('declarations.page_edit_title'))
@section('content')
<div class="page-header"><h4 class="fw-bold mb-0">{{ __('declarations.form_modify_title') }} #{{ $declaration->id }}</h4></div>
<div class="row justify-content-center"><div class="col-md-7">
<div class="card"><div class="card-body p-4">
<form method="POST" action="{{ route('declarations.update', $declaration->id) }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label">{{ __('declarations.form_service_label') }} *</label>
<select name="service_id" class="form-select" required>
@foreach($services as $s)<option value="{{ $s->id }}" {{ $declaration->service_id == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>@endforeach
</select></div>
<div class="mb-3"><label class="form-label">{{ __('declarations.form_container_type') }} *</label>
<select name="type_contenant_id" class="form-select" required>
@foreach($typeContenants as $tc)<option value="{{ $tc->id }}" {{ $declaration->type_contenant_id == $tc->id ? 'selected' : '' }}>{{ $tc->nom }} (~{{ $tc->poids_moyen_kg }} kg)</option>@endforeach
</select></div>
<div class="mb-3"><label class="form-label">{{ __('declarations.form_container_full_count') }} *</label>
<input type="number" name="nombre_contenants" class="form-control" value="{{ $declaration->nombre_contenants }}" min="1" required></div>
<div class="mb-4"><label class="form-label">{{ __('common.notes') }}</label>
<textarea name="notes" class="form-control" rows="2">{{ $declaration->notes }}</textarea></div>
<div class="d-flex gap-2">
<button type="submit" class="btn btn-primary">{{ __('declarations.btn_save_short') }}</button>
<a href="{{ route('declarations.show', $declaration->id) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
</div>
</form></div></div>
</div></div>
@endsection
