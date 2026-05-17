@extends('layouts.app')

@section('title', __('signatures.page_show_title') . ' #' . $signature->id)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="mb-0">
        <i class="bi bi-shield-check"></i> {{ __('signatures.header_show') }}
        @if($signature->revoked_at)
            <span class="badge bg-danger ms-2">{{ __('signatures.badge_revoked') }}</span>
        @endif
    </h2>
    <div class="btn-group">
        <a href="{{ route('signatures.index') }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left"></i> {{ __('signatures.btn_back_to_list') }}
        </a>
        @if($signature->pdfReady())
        <a href="{{ route('signatures.pdf', $signature->id) }}"
           class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-pdf"></i> {{ __('signatures.btn_download_signed_pdf') }}
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
                <i class="bi bi-info-circle"></i> {{ __('signatures.card_info') }}
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <th style="width:42%">{{ __('signatures.lbl_ref_collecte') }}</th>
                            <td>
                                <a href="{{ route('collectes.show', $signature->collecte_id) }}">
                                    {{ $signature->collecte->numero_bordereau ?? '#'.$signature->collecte_id }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('signatures.lbl_etab_full') }}</th>
                            <td>{{ $signature->etablissement->nom ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('signatures.lbl_signer_full') }}</th>
                            <td>
                                <strong>{{ $signature->signataire_nom }}</strong>
                                @if($signature->signataire_fonction)
                                    — {{ $signature->signataire_fonction }}
                                @endif
                                <br><small class="text-muted">
                                    {{ __('signatures.lbl_account_lbt') }}
                                    {{ $signature->signataire ? trim($signature->signataire->prenom.' '.$signature->signataire->nom) : '—' }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('signatures.lbl_agent_witness') }}</th>
                            <td>
                                {{ $signature->agent ? trim($signature->agent->prenom.' '.$signature->agent->nom) : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('signatures.lbl_signed_at_full') }}</th>
                            <td>
                                {{ $signature->signed_at?->format('d/m/Y H:i:s') }}
                                <small class="text-muted">({{ $signature->signed_at?->timezoneName }})</small>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('signatures.lbl_ip_addr') }}</th>
                            <td><code>{{ $signature->ip_address }}</code></td>
                        </tr>
                        <tr>
                            <th>{{ __('signatures.lbl_device') }}</th>
                            <td>
                                <small>
                                    @php $info = $signature->device_info ?? []; @endphp
                                    {{ $info['os'] ?? '—' }} —
                                    {{ $info['browser'] ?? '—' }}
                                    @if(!empty($info['screen_resolution']))
                                        <br>{{ __('signatures.lbl_resolution') }} {{ $info['screen_resolution'] }}
                                    @endif
                                    @if(!empty($info['is_mobile']) || !empty($info['is_tablet']))
                                        <br><span class="badge bg-info">
                                            {{ !empty($info['is_tablet']) ? __('signatures.lbl_tablet') : __('signatures.lbl_mobile') }}
                                        </span>
                                    @endif
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('signatures.lbl_hash') }}</th>
                            <td>
                                <code style="word-break:break-all;font-size:0.75rem">{{ $signature->signature_hash }}</code>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('signatures.lbl_legal_mention') }}</th>
                            <td><em>« {{ $signature->commentaire }} »</em></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($signature->revoked_at)
        <div class="card border-danger mt-3">
            <div class="card-header bg-danger text-white fw-bold">
                <i class="bi bi-x-octagon"></i> {{ __('signatures.card_revoked') }}
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ __('signatures.lbl_revoked_at') }}</strong> {{ $signature->revoked_at->format('d/m/Y H:i:s') }}</p>
                <p class="mb-1"><strong>{{ __('signatures.lbl_revoked_by') }}</strong>
                    {{ $signature->revokedBy
                        ? trim($signature->revokedBy->prenom.' '.$signature->revokedBy->nom)
                        : '—' }}
                </p>
                <p class="mb-0"><strong>{{ __('signatures.lbl_revoke_reason') }}</strong> {{ $signature->revocation_reason }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Carte 2 : Aperçu signature ────────────────────── --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-light fw-bold">
                <i class="bi bi-pen"></i> {{ __('signatures.card_preview') }}
            </div>
            <div class="card-body text-center">
                @if($imgBase64)
                    <img src="{{ $imgBase64 }}" alt="Signature"
                         class="img-fluid border rounded" style="max-height:300px;background:#fff" />
                @else
                    <div class="text-muted py-4">
                        <i class="bi bi-image"></i> {{ __('signatures.preview_unavailable') }}
                    </div>
                @endif
                <div class="mt-2 small text-muted">
                    {{ __('signatures.hash_short') }} <code>{{ $signature->hash_short }}</code>
                </div>
            </div>
        </div>

        {{-- ── Révocation (superadmin) ──────────────────── --}}
        @can('revoke', $signature)
        <div class="card mt-3 border-warning">
            <div class="card-header bg-warning fw-bold">
                <i class="bi bi-exclamation-triangle"></i> {{ __('signatures.card_revoke') }}
            </div>
            <div class="card-body">
                <p class="small text-muted">{{ __('signatures.revoke_hint') }}</p>
                <form method="POST" action="{{ route('signatures.revoke', $signature->id) }}"
                      onsubmit="return confirm('{{ __('signatures.confirm_revoke') }}');">
                    @csrf
                    @method('DELETE')
                    <textarea name="revocation_reason" class="form-control form-control-sm mb-2"
                              rows="2" required maxlength="500"
                              placeholder="{{ __('signatures.revoke_reason_ph') }}"></textarea>
                    <button class="btn btn-danger btn-sm">
                        <i class="bi bi-x-octagon"></i> {{ __('signatures.btn_revoke') }}
                    </button>
                </form>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection
