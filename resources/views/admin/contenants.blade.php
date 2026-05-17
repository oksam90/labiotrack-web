@extends("layouts.app")
@section("title", __('admin.page_containers_title'))
@section("content")
<div class="page-header"><h4 class="fw-bold mb-0"><i class="bi bi-box me-2"></i>{{ __('admin.header_containers') }}</h4></div>
<div class="row g-3">
<div class="col-md-5"><div class="card"><div class="card-header">{{ __('admin.card_add_container') }}</div><div class="card-body">
<form method="POST" action="{{ route("admin.contenants.store") }}">@csrf
@if($errors->any())
<div class="alert alert-danger py-2 mb-2">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif
<div class="mb-2"><label class="form-label">{{ __('admin.col_name') }} *</label><input type="text" name="nom" class="form-control" required></div>
<div class="mb-2"><label class="form-label">{{ __('admin.col_code') }} *</label><input type="text" name="code" class="form-control" required></div>
<div class="mb-2"><label class="form-label">{{ __('admin.col_waste_type') }} *</label><select name="type_dechet_id" class="form-select" required><option value="">{{ __('admin.select_placeholder') }}</option>
@foreach($typeDechets as $td)<option value="{{ $td->id }}">{{ $td->nom }}</option>@endforeach</select></div>
<div class="row g-2 mb-3">
<div class="col-6"><label class="form-label">{{ __('admin.col_avg_weight') }}*</label><input type="number" step="0.01" name="poids_moyen_kg" class="form-control" required></div>
<div class="col-6"><label class="form-label">{{ __('admin.col_unit_cost') }}</label><input type="number" step="0.01" name="cout_unitaire" class="form-control" value="0"></div>
</div>
<button type="submit" class="btn btn-primary btn-sm">{{ __('admin.btn_add') }}</button>
</form></div></div></div>
<div class="col-md-7"><div class="card"><div class="table-responsive"><table class="table mb-0">
<thead><tr>
    <th>{{ __('admin.col_container') }}</th>
    <th>{{ __('admin.col_code') }}</th>
    <th>{{ __('admin.col_avg_weight_short') }}</th>
    <th>{{ __('admin.col_cost') }}</th>
    <th></th>
</tr></thead>
<tbody>
@foreach($contenants as $c)
<tr>
<td><strong>{{ $c->nom }}</strong></td>
<td><code>{{ $c->code }}</code></td>
<td>{{ $c->poids_moyen_kg }} kg</td>
<td>{{ number_format($c->cout_unitaire) }} FCFA</td>
<td>
<div class="d-flex gap-1">
<button type="button" class="btn btn-sm btn-outline-secondary py-0" title="{{ __('admin.btn_modify') }}"
    data-bs-toggle="modal" data-bs-target="#editContenant{{ $c->id }}"><i class="bi bi-pencil"></i></button>
<form method="POST" action="{{ route('admin.contenants.destroy', $c->id) }}" class="d-inline" onsubmit="return confirm('{{ __('admin.confirm_delete_container') }}')">@csrf @method('DELETE')
<button type="submit" class="btn btn-sm btn-outline-danger py-0" title="{{ __('admin.btn_delete') }}"><i class="bi bi-trash"></i></button></form>
</div>
</td>
</tr>
@endforeach
</tbody></table></div>
<div class="card-footer">{{ $contenants->links() }}</div>
</div></div>
</div>

{{-- Modales de modification --}}
@foreach($contenants as $c)
<div class="modal fade" id="editContenant{{ $c->id }}" tabindex="-1" aria-labelledby="editLabel{{ $c->id }}" aria-hidden="true">
<div class="modal-dialog"><div class="modal-content">
<form method="POST" action="{{ route('admin.contenants.update', $c->id) }}">@csrf @method("PUT")
<div class="modal-header">
    <h5 class="modal-title" id="editLabel{{ $c->id }}"><i class="bi bi-pencil me-2"></i>{{ __('admin.card_edit_container') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="mb-3"><label class="form-label">{{ __('admin.col_name') }} *</label><input type="text" name="nom" class="form-control" value="{{ $c->nom }}" required></div>
    <div class="row g-2 mb-3">
        <div class="col-6"><label class="form-label">{{ __('admin.col_avg_weight') }} *</label><input type="number" step="0.01" name="poids_moyen_kg" class="form-control" value="{{ $c->poids_moyen_kg }}" required></div>
        <div class="col-6"><label class="form-label">{{ __('admin.col_unit_cost') }}</label><input type="number" step="0.01" name="cout_unitaire" class="form-control" value="{{ $c->cout_unitaire }}"></div>
    </div>
    <div class="mb-3"><label class="form-label">{{ __('admin.col_description') }}</label><textarea name="description" class="form-control" rows="2">{{ $c->description ?? '' }}</textarea></div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin.btn_cancel') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('admin.btn_save') }}</button>
</div>
</form>
</div></div></div>
@endforeach
@endsection
