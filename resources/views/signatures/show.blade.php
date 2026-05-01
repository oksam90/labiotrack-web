@extends('layouts.app')

@section('title', 'Détail signature #' . $signature->id)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="mb-0">
        <i class="bi bi-shield-check"></i> Détail de preuve numérique
        @if($signature->revoked_at)
            <span class="badge bg-danger ms-2">Révoquée</span>
        @endif
    </h2>
    <div class="btn-group">
        <a href="{{ route('signatures.index') }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
        @if($signature->pdfReady())
        <a href="{{ route('signatures.pdf', $signature->id) }}"
           class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-pdf"></i> Télécharger PDF signé
        </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-3">
    {{-- ── Carte 1 : Informations collecte / signataire ─── --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-light fw-bold">
                <i class="bi bi-info-circle"></i> Informations
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <th style="width:42%">Référence collecte</th>
                            <td>
                                <a href="{{ route('collectes.show', $signature->collecte_id) }}">
                                    {{ $signature->collecte->numero_bordereau ?? '#'.$signature->collecte_id }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Établissement</th>
                            <td>{{ $signature->etablissement->nom ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Signataire</th>
                            <td>
                                <strong>{{ $signature->signataire_nom }}</strong>
                                @if($signature->signataire_fonction)
                                    — {{ $signature->signataire_fonction }}
                                @endif
                                <br><small class="text-muted">
                                    Compte LaBioTrack :
                                    {{ $signature->signataire ? trim($signature->signataire->prenom.' '.$signature->signataire->nom) : '—' }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <th>Agent collecteur (témoin)</th>
                            <td>
                                {{ $signature->agent ? trim($signature->agent->prenom.' '.$signature->agent->nom) : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Date et heure de signature</th>
                            <td>
                                {{ $signature->signed_at?->format('d/m/Y à H:i:s') }}
                                <small class="text-muted">({{ $signature->signed_at?->timezoneName }})</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Adresse IP</th>
                            <td><code>{{ $signature->ip_address }}</code></td>
                        </tr>
                        <tr>
                            <th>Appareil</th>
                            <td>
                                <small>
                                    @php $info = $signature->device_info ?? []; @endphp
                                    {{ $info['os'] ?? '—' }} —
                                    {{ $info['browser'] ?? '—' }}
                                    @if(!empty($info['screen_resolution']))
                                        <br>Résolution : {{ $info['screen_resolution'] }}
                                    @endif
                                    @if(!empty($info['is_mobile']) || !empty($info['is_tablet']))
                                        <br><span class="badge bg-info">
                                            {{ !empty($info['is_tablet']) ? 'Tablette' : 'Mobile' }}
                                        </span>
                                    @endif
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <th>Hash d'intégrité (SHA-256)</th>
                            <td>
                                <code style="word-break:break-all;font-size:0.75rem">{{ $signature->signature_hash }}</code>
                            </td>
                        </tr>
                        <tr>
                            <th>Mention légale</th>
                            <td><em>« {{ $signature->commentaire }} »</em></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($signature->revoked_at)
        <div class="card border-danger mt-3">
            <div class="card-header bg-danger text-white fw-bold">
                <i class="bi bi-x-octagon"></i> Signature révoquée
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Date :</strong> {{ $signature->revoked_at->format('d/m/Y H:i:s') }}</p>
                <p class="mb-1"><strong>Par :</strong>
                    {{ $signature->revokedBy
                        ? trim($signature->revokedBy->prenom.' '.$signature->revokedBy->nom)
                        : '—' }}
                </p>
                <p class="mb-0"><strong>Motif :</strong> {{ $signature->revocation_reason }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Carte 2 : Aperçu signature ────────────────────── --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-light fw-bold">
                <i class="bi bi-pen"></i> Aperçu signature
            </div>
            <div class="card-body text-center">
                @if($imgBase64)
                    <img src="{{ $imgBase64 }}" alt="Signature"
                         class="img-fluid border rounded" style="max-height:300px;background:#fff" />
                @else
                    <div class="text-muted py-4">
                        <i class="bi bi-image"></i> Image indisponible
                    </div>
                @endif
                <div class="mt-2 small text-muted">
                    Hash court : <code>{{ $signature->hash_short }}</code>
                </div>
            </div>
        </div>

        {{-- ── Révocation (superadmin) ──────────────────── --}}
        @can('revoke', $signature)
        <div class="card mt-3 border-warning">
            <div class="card-header bg-warning fw-bold">
                <i class="bi bi-exclamation-triangle"></i> Révoquer la signature
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    La révocation marque la signature comme invalide et remet
                    la collecte au statut « en cours » pour autoriser une nouvelle
                    signature. L'ancien PDF reste archivé.
                </p>
                <form method="POST" action="{{ route('signatures.revoke', $signature->id) }}"
                      onsubmit="return confirm('Confirmer la révocation ?');">
                    @csrf
                    @method('DELETE')
                    <textarea name="revocation_reason" class="form-control form-control-sm mb-2"
                              rows="2" required maxlength="500"
                              placeholder="Motif de révocation (obligatoire)"></textarea>
                    <button class="btn btn-danger btn-sm">
                        <i class="bi bi-x-octagon"></i> Révoquer
                    </button>
                </form>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection
