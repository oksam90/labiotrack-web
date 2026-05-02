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
            // Hydrate un modèle Eloquent pour la policy (la $collecte courante
            // vient de DB::table() → c'est un stdClass)
            $collecteModel = \App\Models\Collecte::find($collecte->id);
        @endphp
        @if($collecteModel && ! $signature && Auth::user()->can('signatureOpen', $collecteModel))
        <a href="{{ route('signatures.create', $collecte->id) }}" class="btn btn-success btn-sm">
            <i class="bi bi-pen me-1"></i>Signature électronique
        </a>
        @endif
        @if($signature && Auth::user()->can('view', $signature))
        <a href="{{ route('signatures.show', $signature->id) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-shield-check me-1"></i>Voir signature
        </a>
        @endif
        @if(!$destruction && in_array(Auth::user()->role,['prestataire','admin','superadmin','admin_reseau']))
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
    @php $colors = ['planifie'=>'secondary','en_cours'=>'primary','signee'=>'success','complete'=>'success','annule'=>'danger']; @endphp
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
{{-- Le bloc « Valider la collecte (double signature) » a été retiré :
     la validation passe désormais par la signature électronique
     (bouton « Signature électronique » en en-tête, qui crée une
     entrée signatures + bascule la collecte au statut « signee »). --}}
@endsection
