@extends("layouts.app")
@section("title","Transfert")
@section("content")
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-archive me-2"></i>Transfert #{{ $transfert->id }}</h4>
    <a href="{{ route('stockage.index') }}" class="btn btn-sm btn-outline-secondary">← Retour</a>
</div>
<div class="card"><div class="card-body">
<p><strong>Zone :</strong> {{ $transfert->zone_stockage }}</p>
<p><strong>Date limite :</strong> {{ $transfert->date_limite_collecte }}</p>
@php $statutColors = ['en_attente'=>'warning','valide'=>'success','collecte'=>'info']; @endphp
<p><strong>Statut :</strong> <span class="badge bg-{{ $statutColors[$transfert->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$transfert->statut)) }}</span></p>

@if($transfert->statut === 'en_attente')
<form method="POST" action="{{ route('stockage.valider', $transfert->id) }}">@csrf
<button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Valider la réception</button></form>
@else
<button type="button" class="btn btn-success disabled" disabled><i class="bi bi-check-circle me-1"></i>Réception validée</button>
@endif
</div></div>
@endsection
