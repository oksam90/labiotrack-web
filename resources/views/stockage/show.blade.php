@extends("layouts.app")
@section("title","Transfert")
@section("content")
<div class="page-header"><h4>Transfert #{{ $transfert->id }}</h4></div>
<div class="card"><div class="card-body">
<p><strong>Zone :</strong> {{ $transfert->zone_stockage }}</p>
<p><strong>Date limite :</strong> {{ $transfert->date_limite_collecte }}</p>
<p><strong>Statut :</strong> {{ $transfert->statut }}</p>
<form method="POST" action="{{ route("stockage.valider", $transfert->id) }}">@csrf
<button type="submit" class="btn btn-success">Valider la réception</button></form>
</div></div>
@endsection