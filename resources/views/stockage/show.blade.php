@extends("layouts.app")
@section("title", __('stockage.page_show_title'))
@section("content")
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-archive me-2"></i>{{ __('stockage.show_header') }} #{{ $transfert->id }}</h4>
    <a href="{{ route('stockage.index') }}" class="btn btn-sm btn-outline-secondary">← {{ __('stockage.btn_back') }}</a>
</div>
<div class="card"><div class="card-body">
<p><strong>{{ __('stockage.show_zone') }}</strong> {{ $transfert->zone_stockage }}</p>
<p><strong>{{ __('stockage.show_deadline') }}</strong> {{ $transfert->date_limite_collecte }}</p>
@php $statutColors = ['en_attente'=>'warning','valide'=>'success','collecte'=>'info']; @endphp
<p><strong>{{ __('stockage.show_status') }}</strong> <span class="badge bg-{{ $statutColors[$transfert->statut] ?? 'secondary' }}">{{ __('stockage.status_' . $transfert->statut) }}</span></p>

@if($transfert->statut === 'en_attente')
<form method="POST" action="{{ route('stockage.valider', $transfert->id) }}">@csrf
<button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>{{ __('stockage.btn_validate_receipt') }}</button></form>
@else
<button type="button" class="btn btn-success disabled" disabled><i class="bi bi-check-circle me-1"></i>{{ __('stockage.btn_receipt_validated') }}</button>
@endif
</div></div>
@endsection
