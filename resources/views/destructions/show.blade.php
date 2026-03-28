@extends('layouts.app')
@section('title', 'Détail destruction')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-fire me-2 text-danger"></i>Destruction — Détail</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('destructions.certificat', $destruction->id) }}" class="btn btn-success btn-sm">
            <i class="bi bi-award me-1"></i>Voir certificat
        </a>
        <a href="{{ route('destructions.certificat.pdf', $destruction->id) }}" class="btn btn-danger btn-sm">
            <i class="bi bi-file-pdf me-1"></i>Télécharger PDF
        </a>
        <a href="{{ route('destructions.index') }}" class="btn btn-light btn-sm">← Retour</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2"></i>Informations</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted w-50">Certificat n°</td><td><code class="fw-bold">{{ $destruction->certificat_numero }}</code></td></tr>
                    <tr><td class="text-muted">Bordereau</td><td>{{ $destruction->numero_bordereau }}</td></tr>
                    <tr><td class="text-muted">Méthode</td><td>
                        @php $m=['incineration'=>'🔥 Incinération','autoclave'=>'♨️ Autoclave','desinfection_chimique'=>'🧪 Désinfection chimique','autre'=>'Autre']; @endphp
                        {{ $m[$destruction->methode] ?? $destruction->methode }}
                    </td></tr>
                    <tr><td class="text-muted">Site traitement</td><td>{{ $destruction->site_traitement ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Date réception</td><td>{{ $destruction->date_reception ? \Carbon\Carbon::parse($destruction->date_reception)->format('d/m/Y') : '—' }}</td></tr>
                    <tr><td class="text-muted">Date destruction</td><td>{{ \Carbon\Carbon::parse($destruction->date_destruction)->format('d/m/Y') }}</td></tr>
                    <tr><td class="text-muted">Poids réel</td><td><strong>{{ number_format($destruction->poids_reel_kg, 1) }} kg</strong></td></tr>
                    <tr><td class="text-muted">Conforme</td><td><span class="badge bg-{{ $destruction->conforme ? 'success' : 'danger' }}">{{ $destruction->conforme ? '✓ Oui' : '✗ Non' }}</span></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-building me-2"></i>Établissement</div>
            <div class="card-body">
                <p class="mb-1 fw-semibold">{{ $etablissement->nom }}</p>
                <p class="mb-1 text-muted">{{ $etablissement->adresse }}</p>
                <p class="mb-0 text-muted">{{ $etablissement->ville }}</p>
            </div>
        </div>
    </div>
    @if($destruction->notes)
    <div class="col-12">
        <div class="card">
            <div class="card-header fw-semibold">Notes</div>
            <div class="card-body">{{ $destruction->notes }}</div>
        </div>
    </div>
    @endif
</div>
@endsection
