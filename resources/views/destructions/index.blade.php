@extends('layouts.app')
@section('title', __('destructions.page_index_title'))
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-fire me-2 text-danger"></i>{{ __('destructions.header_index') }}</h4>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('destructions.col_certificat') }}</th>
                    <th>{{ __('destructions.col_bordereau') }}</th>
                    <th>{{ __('destructions.col_date_destruction') }}</th>
                    <th>{{ __('destructions.col_method') }}</th>
                    <th>{{ __('destructions.col_weight_real') }}</th>
                    <th>{{ __('destructions.col_provider') }}</th>
                    <th>{{ __('destructions.col_conform') }}</th>
                    <th>{{ __('destructions.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($destructions as $d)
                <tr>
                    <td><code>{{ $d->certificat_numero ?? '—' }}</code></td>
                    <td><small>{{ $d->numero_bordereau }}</small></td>
                    <td>{{ \Carbon\Carbon::parse($d->date_destruction)->format('d/m/Y') }}</td>
                    <td>{{ __('destructions.method_' . match($d->methode){'incineration'=>'incineration','autoclave'=>'autoclave','desinfection_chimique'=>'desinfection','autre'=>'other',default=>'other'}) }}</td>
                    <td><strong>{{ number_format($d->poids_reel_kg, 1) }}</strong></td>
                    <td><small>{{ $d->prestataire_nom ?? '—' }}</small></td>
                    <td>
                        <span class="badge bg-{{ $d->conforme ? 'success' : 'danger' }}">
                            {{ $d->conforme ? __('destructions.conform_yes') : __('destructions.conform_no') }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('destructions.certificat', $d->id) }}" class="btn btn-sm btn-outline-success py-0" title="{{ __('destructions.btn_view_cert') }}"><i class="bi bi-award"></i></a>
                        <a href="{{ route('destructions.certificat.pdf', $d->id) }}" class="btn btn-sm btn-outline-danger py-0" title="PDF"><i class="bi bi-file-pdf"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-inbox d-block fs-3 mb-2"></i>{{ __('destructions.empty_list') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($destructions->hasPages())
    <div class="card-footer bg-white">{{ $destructions->links() }}</div>
    @endif
</div>
@endsection
