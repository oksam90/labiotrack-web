@extends('layouts.app')
@section('title', __('admin.page_etabs_title'))
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-building me-2"></i>{{ __('admin.page_etabs_title') }}</h4>
    <a href="{{ route('admin.create') }}" class="btn btn-primary"><i class="bi bi-plus me-1"></i>{{ __('admin.btn_add') }}</a>
</div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
<thead><tr>
    <th>{{ __('admin.col_name') }}</th>
    <th>{{ __('admin.col_reseau') }}</th>
    <th>{{ __('admin.col_type') }}</th>
    <th>{{ __('admin.col_city') }}</th>
    <th>{{ __('admin.col_qhse') }}</th>
    <th>{{ __('admin.col_beds') }}</th>
    <th>{{ __('admin.col_status') }}</th>
    <th></th>
</tr></thead>
<tbody>
@foreach($etablissements as $e)
<tr>
<td><strong>{{ $e->nom }}</strong></td>
<td><small class="text-muted">{{ $e->reseau_nom ?? '—' }}</small></td>
<td><span class="badge bg-info">{{ __('admin.etab_type_' . $e->type) ?? ucfirst($e->type) }}</span></td>
<td>{{ $e->ville }}</td>
<td>{{ $e->responsable_qhse }}</td>
<td>{{ $e->nombre_lits }}</td>
<td>@if($e->actif)<span class="badge bg-success">{{ __('admin.status_active') }}</span>@else<span class="badge bg-secondary">{{ __('admin.status_inactive') }}</span>@endif</td>
<td class="d-flex gap-1">
<a href="{{ route('admin.edit', $e->id) }}" class="btn btn-sm btn-outline-secondary py-0" title="{{ __('admin.btn_modify') }}"><i class="bi bi-pencil"></i></a>
@if(auth()->user()->isSuperAdmin())
<form method="POST" action="{{ route('admin.toggle', $e->id) }}" class="d-inline">@csrf
<button type="submit" class="btn btn-sm {{ $e->actif ? 'btn-outline-warning' : 'btn-outline-success' }} py-0" title="{{ $e->actif ? __('admin.btn_deactivate') : __('admin.btn_activate') }}"><i class="bi {{ $e->actif ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i></button></form>
<form method="POST" action="{{ route('admin.destroy', $e->id) }}" class="d-inline" onsubmit="return confirm('{{ __('admin.confirm_delete_etab') }}')">@csrf @method('DELETE')
<button type="submit" class="btn btn-sm btn-outline-danger py-0" title="{{ __('admin.btn_delete') }}"><i class="bi bi-trash"></i></button></form>
@endif
</td>
</tr>
@endforeach
</tbody>
</table></div><div class="card-footer">{{ $etablissements->links() }}</div></div>
@endsection
