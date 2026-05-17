@extends('layouts.app')
@section('title', $etablissement ? __('admin.page_etab_edit_title') : __('admin.page_etab_new_title'))
@section('content')
<div class="page-header"><h4>{{ $etablissement ? __('admin.header_etab_form_edit') : __('admin.header_etab_form_new') }}</h4></div>
<div class="row justify-content-center"><div class="col-md-7">
<div class="card"><div class="card-body p-4">
<form method="POST" action="{{ $etablissement ? route('admin.update', $etablissement->id) : route('admin.store') }}">
@csrf @if($etablissement) @method('PUT') @endif
<div class="row g-3">
<div class="col-md-8"><label class="form-label">{{ __('admin.col_name') }} *</label><input type="text" name="nom" class="form-control" value="{{ $etablissement->nom ?? '' }}" required></div>
<div class="col-md-4"><label class="form-label">{{ __('admin.col_type') }} *</label>
<select name="type" class="form-select" required>
@foreach(['clinique','hopital','cabinet','laboratoire'] as $t)
<option value="{{ $t }}" {{ ($etablissement->type ?? '') === $t ? 'selected' : '' }}>{{ __('admin.etab_type_' . $t) }}</option>
@endforeach
</select></div>
<div class="col-12"><label class="form-label">{{ __('admin.col_address') }} *</label><textarea name="adresse" class="form-control" required>{{ $etablissement->adresse ?? '' }}</textarea></div>
<div class="col-md-6"><label class="form-label">{{ __('admin.col_city') }}</label><input type="text" name="ville" class="form-control" value="{{ $etablissement->ville ?? '' }}"></div>
<div class="col-md-6"><label class="form-label">{{ __('admin.col_phone') }}</label><input type="text" name="telephone" class="form-control" value="{{ $etablissement->telephone ?? '' }}"></div>
<div class="col-md-6"><label class="form-label">{{ __('admin.col_email') }}</label><input type="email" name="email" class="form-control" value="{{ $etablissement->email ?? '' }}"></div>
<div class="col-md-6"><label class="form-label">{{ __('admin.col_responsable_qhse') }}</label><input type="text" name="responsable_qhse" class="form-control" value="{{ $etablissement->responsable_qhse ?? '' }}"></div>
<div class="col-md-4"><label class="form-label">{{ __('admin.col_nb_beds') }}</label><input type="number" name="nombre_lits" class="form-control" value="{{ $etablissement->nombre_lits ?? 0 }}"></div>
@if(isset($reseaux) && $reseaux->count() > 0)
<div class="col-md-8"><label class="form-label">{{ __('admin.col_reseau_attach') }}
@if(auth()->user()->isAdminReseau()) <small class="text-muted">{{ __('admin.col_reseau_hint_my') }}</small>@endif
</label>
<select name="reseau_id" class="form-select" {{ auth()->user()->isAdminReseau() ? 'disabled' : '' }}>
<option value="">{{ __('admin.select_none_reseau') }}</option>
@foreach($reseaux as $r)
<option value="{{ $r->id }}" {{ ($etablissement->reseau_id ?? (auth()->user()->isAdminReseau() ? auth()->user()->reseau_id : null)) == $r->id ? 'selected' : '' }}>{{ $r->nom }}</option>
@endforeach
</select>
@if(auth()->user()->isAdminReseau())
<input type="hidden" name="reseau_id" value="{{ auth()->user()->reseau_id }}">
@endif
</div>
@endif
</div>
<div class="d-flex gap-2 mt-4">
<button type="submit" class="btn btn-primary">{{ __('admin.btn_save') }}</button>
<a href="{{ route('admin.index') }}" class="btn btn-outline-secondary">{{ __('admin.btn_cancel') }}</a>
</div>
</form></div></div></div></div>
@endsection
