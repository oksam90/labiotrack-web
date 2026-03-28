{{-- declarations/index.blade.php --}}
@extends('layouts.app')
@section('title','Déclarations')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-0">Déclarations de déchets</h4>
        <small class="text-muted">Suivi de la production par service</small>
    </div>
    <a href="{{ route('declarations.create') }}" class="btn btn-primary"><i class="bi bi-plus me-1"></i>Nouvelle déclaration</a>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.8rem;">Service</label>
                <select name="service_id" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($services as $s)<option value="{{ $s->id }}" {{ request('service_id')==$s->id?'selected':'' }}>{{ $s->nom }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.8rem;">Contenant</label>
                <select name="type_contenant_id" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($typeContenants as $tc)<option value="{{ $tc->id }}" {{ request('type_contenant_id')==$tc->id?'selected':'' }}>{{ $tc->nom }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.8rem;">Statut</label>
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="en_stock" {{ request('statut')=='en_stock'?'selected':'' }}>En stock</option>
                    <option value="en_transport" {{ request('statut')=='en_transport'?'selected':'' }}>En transport</option>
                    <option value="detruit" {{ request('statut')=='detruit'?'selected':'' }}>Détruit</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.8rem;">Du</label>
                <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.8rem;">Au</label>
                <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-funnel"></i> Filtrer</button>
                <a href="{{ route('declarations.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th><th>Date</th><th>Service</th><th>Contenant</th>
                    <th>Qté</th><th>Poids est.</th><th>Statut</th><th>Agent</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($declarations as $d)
                <tr>
                    <td><small class="text-muted">{{ $d->id }}</small></td>
                    <td>
                        <div>{{ \Carbon\Carbon::parse($d->date_declaration)->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ substr($d->heure_declaration,0,5) }}</small>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ $d->service_nom }}</span></td>
                    <td><small>{{ $d->contenant_nom }}</small></td>
                    <td><strong>{{ $d->nombre_contenants }}</strong></td>
                    <td>{{ number_format($d->poids_estime_kg,1) }} kg</td>
                    <td><span class="statut-badge statut-{{ $d->statut }}">{{ str_replace('_',' ',ucfirst($d->statut)) }}</span></td>
                    <td><small class="text-muted">{{ $d->agent_nom }}</small></td>
                    <td>
                        <a href="{{ route('declarations.show', $d->id) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2"><i class="bi bi-eye"></i></a>
                        @if($d->statut === 'en_stock')
                        <a href="{{ route('declarations.edit', $d->id) }}" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-2"><i class="bi bi-pencil"></i></a>
                        @endif
                    </td>
                </tr>
                @endforeach
                @if($declarations->isEmpty())
                <tr><td colspan="9" class="text-center text-muted py-4">Aucune déclaration trouvée</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">{{ $declarations->total() }} résultat(s)</small>
        {{ $declarations->withQueryString()->links() }}
    </div>
</div>
@endsection
