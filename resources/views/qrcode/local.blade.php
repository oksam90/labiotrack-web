@extends("layouts.app")
@section("title","QR Code Local")
@section("content")
<div class="page-header"><h4>QR Code — Local de stockage</h4></div>
<div class="row justify-content-center"><div class="col-md-4 text-center">
<div class="card"><div class="card-body p-4">
<h5>{{ $etablissement->nom }}</h5>
<p class="text-muted">À afficher dans le local de stockage central</p>
{!! QrCode::size(250)->generate($qrData) !!}
<button onclick="window.print()" class="btn btn-sm btn-primary mt-3"><i class="bi bi-printer me-1"></i>Imprimer</button>
</div></div></div></div>
@endsection