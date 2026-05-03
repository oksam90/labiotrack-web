@extends('layouts.app')
@section('title','Collectes')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-truck me-2 text-primary"></i>Gestion des collectes</h4>
    {{-- client_signataire EXCLU : son rôle est de signer, pas de créer --}}
    @if(in_array(Auth::user()->role, ['admin','qhse','collecteur']))
    <a href="{{ route('collectes.create') }}" class="btn btn-primary"><i class="bi bi-plus me-1"></i>Nouvelle collecte</a>
    @endif
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Bordereau</th><th>Date</th><th>Collecteur</th><th>Contenants</th><th>Poids déclaré</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($collectes as $c)
                <tr>
                    <td><code>{{ $c->numero_bordereau }}</code></td>
                    <td>{{ \Carbon\Carbon::parse($c->date_collecte)->format('d/m/Y H:i') }}</td>
                    <td><small>{{ $c->collecteur_nom ?? '—' }}</small></td>
                    <td><strong>{{ $c->nombre_contenants }}</strong></td>
                    <td>{{ number_format($c->poids_declare_kg, 1) }} kg</td>
                    <td>
                        @php $colors = ['planifie'=>'secondary','en_cours'=>'primary','signee'=>'success','complete'=>'success','annule'=>'danger']; @endphp
                        <span class="badge bg-{{ $colors[$c->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$c->statut)) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('collectes.show', $c->id) }}" class="btn btn-sm btn-outline-primary py-0" title="Voir détail"><i class="bi bi-eye"></i></a>
                        <form method="POST" action="{{ route('collectes.bordereau', $c->id) }}" class="d-inline">@csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary py-0" title="Télécharger bordereau"><i class="bi bi-file-pdf"></i></button>
                        </form>
                        {{-- Bouton Signer : visible si la policy l'autorise (statut en_cours, pas de signature, rôle ok) --}}
                        @if($c->statut === 'en_cours')
                            @php $cm = \App\Models\Collecte::find($c->id); @endphp
                            @if($cm && Auth::user()->can('signatureOpen', $cm))
                            <a href="{{ route('signatures.create', $c->id) }}"
                               class="btn btn-sm btn-outline-success py-0" title="Signer le bordereau">
                                <i class="bi bi-pen"></i>
                            </a>
                            @endif
                        @endif
                    </td>
                </tr>
                @endforeach
                @if($collectes->isEmpty())
                <tr><td colspan="7" class="text-center text-muted py-4">Aucune collecte enregistrée</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $collectes->links() }}</div>
</div>
@endsection
