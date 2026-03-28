@extends('layouts.app')
@section('title', 'Destructions')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-fire me-2 text-danger"></i>Destructions & Certificats</h4>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Certificat</th>
                    <th>Bordereau</th>
                    <th>Date destruction</th>
                    <th>Méthode</th>
                    <th>Poids réel (kg)</th>
                    <th>Prestataire</th>
                    <th>Conforme</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($destructions as $d)
                <tr>
                    <td><code>{{ $d->certificat_numero ?? '—' }}</code></td>
                    <td><small>{{ $d->numero_bordereau }}</small></td>
                    <td>{{ \Carbon\Carbon::parse($d->date_destruction)->format('d/m/Y') }}</td>
                    <td>
                        @php $methodes = ['incineration'=>'🔥 Incinération','autoclave'=>'♨️ Autoclave','desinfection_chimique'=>'🧪 Désinfection','autre'=>'Autre']; @endphp
                        {{ $methodes[$d->methode] ?? $d->methode }}
                    </td>
                    <td><strong>{{ number_format($d->poids_reel_kg, 1) }}</strong></td>
                    <td><small>{{ $d->prestataire_nom ?? '—' }}</small></td>
                    <td>
                        <span class="badge bg-{{ $d->conforme ? 'success' : 'danger' }}">
                            {{ $d->conforme ? '✓ Oui' : '✗ Non' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('destructions.certificat', $d->id) }}" class="btn btn-sm btn-outline-success py-0" title="Voir certificat"><i class="bi bi-award"></i></a>
                        <a href="{{ route('destructions.certificat.pdf', $d->id) }}" class="btn btn-sm btn-outline-danger py-0" title="PDF"><i class="bi bi-file-pdf"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-inbox d-block fs-3 mb-2"></i>Aucune destruction enregistrée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($destructions->hasPages())
    <div class="card-footer bg-white">{{ $destructions->links() }}</div>
    @endif
</div>
@endsection
