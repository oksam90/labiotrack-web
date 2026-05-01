@extends('layouts.app')
@section('title','Détail Collecte')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0">Collecte — <code>{{ $collecte->numero_bordereau }}</code></h4>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('collectes.bordereau', $collecte->id) }}">@csrf
        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-file-pdf me-1"></i>Bordereau PDF</button></form>
        @php
            $signature = \App\Models\Signature::where('collecte_id', $collecte->id)->first();
            $canSign = $collecte->statut === 'en_cours' && ! $signature
                && in_array(Auth::user()->role, ['qhse','agent','collecteur','superadmin']);
        @endphp
        @if($canSign)
        <a href="{{ route('signatures.create', $collecte->id) }}" class="btn btn-success btn-sm">
            <i class="bi bi-pen me-1"></i>Signature électronique
        </a>
        @endif
        @if($signature)
        <a href="{{ route('signatures.show', $signature->id) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-shield-check me-1"></i>Voir signature
        </a>
        @endif
        @if(!$destruction && in_array(Auth::user()->role,['prestataire','qhse','admin']))
        <a href="{{ route('destructions.create', $collecte->id) }}" class="btn btn-danger btn-sm"><i class="bi bi-fire me-1"></i>Confirmer destruction</a>
        @endif
    </div>
</div>
<div class="row g-3">
<div class="col-md-4">
<div class="card"><div class="card-header">Informations</div><div class="card-body">
    <p><strong>Bordereau :</strong> <code>{{ $collecte->numero_bordereau }}</code></p>
    <p><strong>Date :</strong> {{ \Carbon\Carbon::parse($collecte->date_collecte)->format('d/m/Y H:i') }}</p>
    <p><strong>Collecteur :</strong> {{ $collecte->collecteur_nom ?? '—' }}</p>
    <p><strong>Contenants :</strong> {{ $collecte->nombre_contenants }}</p>
    <p><strong>Poids déclaré :</strong> {{ number_format($collecte->poids_declare_kg,1) }} kg</p>
    <p><strong>Véhicule :</strong> {{ $collecte->vehicule ?? '—' }}</p>
    @php $colors = ['planifie'=>'secondary','en_cours'=>'primary','signee'=>'info','complete'=>'success','annule'=>'danger']; @endphp
    <p><strong>Statut :</strong> <span class="badge bg-{{ $colors[$collecte->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$collecte->statut)) }}</span></p>
    @if($destruction)
    <hr>
    <p class="mb-0"><strong>Destruction :</strong> <a href="{{ route('destructions.certificat', $destruction->id) }}" class="btn btn-sm btn-success py-0"><i class="bi bi-award"></i> Voir certificat</a></p>
    @endif
</div></div>
</div>
<div class="col-md-8">
<div class="card"><div class="card-header">Déclarations incluses ({{ $declarations->count() }})</div>
<div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Service</th><th>Contenant</th><th>Qté</th><th>Poids est.</th></tr></thead>
<tbody>
@foreach($declarations as $d)
<tr>
    <td>{{ $d->service_nom }}</td><td><small>{{ $d->contenant_nom }}</small></td>
    <td>{{ $d->nombre_contenants }}</td><td>{{ number_format($d->poids_estime_kg,1) }} kg</td>
</tr>
@endforeach
</tbody>
<tfoot><tr class="fw-bold table-light"><td colspan="2">TOTAL</td><td>{{ $declarations->sum('nombre_contenants') }}</td><td>{{ number_format($declarations->sum('poids_estime_kg'),1) }} kg</td></tr></tfoot>
</table></div>
</div>
</div>
</div>
@if($collecte->statut === 'en_cours')
<div class="card mt-3"><div class="card-header">Valider la collecte (double signature)</div><div class="card-body">
<form method="POST" action="{{ route('collectes.valider', $collecte->id) }}">@csrf
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Signature établissement</label><input type="text" name="signature_etablissement" class="form-control" placeholder="Nom complet + validation" required></div>
<div class="col-md-6"><label class="form-label">Signature collecteur</label><input type="text" name="signature_collecteur" class="form-control" placeholder="Nom complet + validation" required></div>
</div>
<button type="submit" class="btn btn-primary mt-3"><i class="bi bi-check-circle me-1"></i>Valider la collecte</button>
</form></div></div>
@endif
@endsection
