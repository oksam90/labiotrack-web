@extends('layouts.app')
@section('title', 'Stockage Central')

@section('content')
<div class="topbar">
    <span class="topbar-title"><i class="bi bi-archive me-2"></i>Stockage Central</span>
    <a href="{{ route('stockage.create') }}" class="btn btn-sm" style="background:var(--primary);color:#fff;border-radius:8px">
        <i class="bi bi-plus-lg me-1"></i>Nouveau transfert
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- KPI rapide --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;background:#e8f5ee;border-radius:10px;display:flex;align-items:center;justify-content:center">
                    <i class="bi bi-box-seam" style="color:var(--primary);font-size:1.2rem"></i>
                </div>
                <div>
                    <div style="font-size:1.5rem;font-weight:700;color:var(--primary)">{{ $enStock }}</div>
                    <div style="font-size:.8rem;color:#6c757d">Déclarations en stock</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" class="card border-0 shadow-sm p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1">Service</label>
            <select name="service" class="form-select form-select-sm">
                <option value="">Tous les services</option>
                @foreach($services as $s)
                    <option value="{{ $s->id }}" {{ request('service') == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Statut</label>
            <select name="statut" class="form-select form-select-sm">
                <option value="">Tous</option>
                <option value="en_attente" {{ request('statut')=='en_attente' ? 'selected' : '' }}>En attente</option>
                <option value="valide"     {{ request('statut')=='valide'     ? 'selected' : '' }}>Validé</option>
                <option value="collecte"   {{ request('statut')=='collecte'   ? 'selected' : '' }}>Collecté</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-secondary w-100">
                <i class="bi bi-search me-1"></i>Filtrer
            </button>
        </div>
    </div>
</form>

{{-- TABLE --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.875rem">
            <thead style="background:#f8faf9">
                <tr>
                    <th class="ps-3">#</th>
                    <th>Service</th>
                    <th>Contenants</th>
                    <th>Poids estimé</th>
                    <th>Zone stockage</th>
                    <th>Date limite</th>
                    <th>Statut</th>
                    <th>Agent</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($transferts as $t)
                <tr>
                    <td class="ps-3 fw-bold text-muted">{{ $t->id }}</td>
                    <td>{{ $t->service_nom }}</td>
                    <td><strong>{{ $t->nombre_contenants }}</strong></td>
                    <td>{{ number_format($t->poids_total_kg, 1) }} kg</td>
                    <td>{{ $t->zone_stockage ?? '—' }}</td>
                    <td>
                        @if($t->date_limite_collecte)
                            @php $diff = (int) \Carbon\Carbon::parse($t->date_limite_collecte)->startOfDay()->diffInDays(now()->startOfDay(), false) @endphp
                            <span class="{{ $diff > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ \Carbon\Carbon::parse($t->date_limite_collecte)->format('d/m/Y') }}
                                @if($diff > 0) <i class="bi bi-exclamation-triangle-fill text-danger"></i> @endif
                            </span>
                        @else —
                        @endif
                    </td>
                    <td>
                        @php $colors = ['en_attente'=>'warning','valide'=>'success','collecte'=>'info'] @endphp
                        <span class="badge bg-{{ $colors[$t->statut] ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_',' ',$t->statut)) }}
                        </span>
                    </td>
                    <td class="text-muted" style="font-size:.8rem">{{ $t->agent_nom }}</td>
                    <td class="text-muted" style="font-size:.78rem">
                        {{ \Carbon\Carbon::parse($t->date_transfert)->format('d/m H:i') }}
                    </td>
                    <td>
                        <a href="{{ route('stockage.show', $t->id) }}" class="btn btn-xs btn-light border">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                        Aucun transfert enregistré.
                        <a href="{{ route('stockage.create') }}" style="color:var(--primary)">Créer le premier →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transferts->hasPages())
    <div class="card-footer bg-white border-top">{{ $transferts->links() }}</div>
    @endif
</div>
@endsection
