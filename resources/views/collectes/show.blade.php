@extends('layouts.app')
@section('title', __('collectes.page_show_title'))
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0">{{ __('collectes.header_show') }} — <code>{{ $collecte->numero_bordereau }}</code></h4>
    <div class="d-flex gap-2">
        @if($collecte->bordereau_generated_at)
            <a href="{{ route('collectes.bordereau.download', $collecte->id) }}" class="btn btn-danger btn-sm">
                <i class="bi bi-file-pdf me-1"></i>{{ __('collectes.btn_bordereau_download') }}
            </a>
            <form method="POST" action="{{ route('collectes.bordereau', $collecte->id) }}" title="{{ __('collectes.btn_bordereau_regenerate') }}">@csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></button></form>
        @else
            <form method="POST" action="{{ route('collectes.bordereau', $collecte->id) }}">@csrf
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-file-pdf me-1"></i>{{ __('collectes.btn_bordereau_pdf') }}</button></form>
        @endif
        @php
            $signature = \App\Models\Signature::where('collecte_id', $collecte->id)->first();
            // Hydrate un modèle Eloquent pour la policy (la $collecte courante
            // vient de DB::table() → c'est un stdClass)
            $collecteModel = \App\Models\Collecte::find($collecte->id);
        @endphp
        @if($collecteModel && ! $signature && Auth::user()->can('signatureOpen', $collecteModel))
        <a href="{{ route('signatures.create', $collecte->id) }}" class="btn btn-success btn-sm">
            <i class="bi bi-pen me-1"></i>{{ __('collectes.btn_sign') }}
        </a>
        @endif
        @if($signature && Auth::user()->can('view', $signature))
        <a href="{{ route('signatures.show', $signature->id) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-shield-check me-1"></i>{{ __('collectes.btn_view_signature') }}
        </a>
        @endif
        @if(!$destruction && in_array(Auth::user()->role,['prestataire','admin','superadmin','admin_reseau']))
        <a href="{{ route('destructions.create', $collecte->id) }}" class="btn btn-danger btn-sm"><i class="bi bi-fire me-1"></i>{{ __('collectes.btn_confirm_destruction') }}</a>
        @endif
    </div>
</div>
<div class="row g-3">
<div class="col-md-4">
<div class="card"><div class="card-header">{{ __('collectes.show_info') }}</div><div class="card-body">
    <p><strong>{{ __('collectes.show_bordereau') }}</strong> <code>{{ $collecte->numero_bordereau }}</code></p>
    <p><strong>{{ __('collectes.show_date') }}</strong> {{ \Carbon\Carbon::parse($collecte->date_collecte)->format('d/m/Y H:i') }}</p>
    <p><strong>{{ __('collectes.show_collecteur') }}</strong> {{ $collecte->collecteur_nom ?? '—' }}</p>
    <p><strong>{{ __('collectes.show_containers') }}</strong> {{ $collecte->nombre_contenants }}</p>
    <p><strong>{{ __('collectes.show_weight_declared') }}</strong> {{ number_format($collecte->poids_declare_kg,1) }} kg</p>
    <p><strong>{{ __('collectes.show_vehicle') }}</strong> {{ $collecte->vehicule ?? '—' }}</p>
    @php $colors = ['planifie'=>'secondary','en_cours'=>'primary','signee'=>'success','complete'=>'success','annule'=>'danger']; @endphp
    <p><strong>{{ __('collectes.show_status') }}</strong> <span class="badge bg-{{ $colors[$collecte->statut] ?? 'secondary' }}">{{ __('collectes.status_' . $collecte->statut) }}</span></p>
    @if($destruction)
    <hr>
    <p class="mb-0"><strong>{{ __('collectes.show_destruction') }}</strong> <a href="{{ route('destructions.certificat', $destruction->id) }}" class="btn btn-sm btn-success py-0"><i class="bi bi-award"></i> {{ __('collectes.show_destruction_cert') }}</a></p>
    @endif
</div></div>
</div>
<div class="col-md-8">
<div class="card"><div class="card-header">{{ __('collectes.show_decl_included') }} ({{ $declarations->count() }})</div>
<div class="table-responsive"><table class="table mb-0">
<thead><tr>
    <th>{{ __('collectes.col_service') }}</th>
    <th>{{ __('collectes.col_container_type') }}</th>
    <th>{{ __('collectes.col_qty') }}</th>
    <th>{{ __('collectes.col_weight_estimated') }}</th>
</tr></thead>
<tbody>
@foreach($declarations as $d)
<tr>
    <td>{{ $d->service_nom }}</td><td><small>{{ $d->contenant_nom }}</small></td>
    <td>{{ $d->nombre_contenants }}</td><td>{{ number_format($d->poids_estime_kg,1) }} kg</td>
</tr>
@endforeach
</tbody>
<tfoot><tr class="fw-bold table-light"><td colspan="2">{{ __('collectes.show_total') }}</td><td>{{ $declarations->sum('nombre_contenants') }}</td><td>{{ number_format($declarations->sum('poids_estime_kg'),1) }} kg</td></tr></tfoot>
</table></div>
</div>
</div>
</div>
@endsection
