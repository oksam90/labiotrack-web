@extends('layouts.app')
@section('title', __('account.page_title'))
@section('content')
<div class="page-header">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-vcard me-2 text-success"></i>{{ __('account.header') }}</h4>
    <small class="text-muted">{{ __('account.subtitle') }}</small>
</div>

<div class="row g-3 justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-person me-2 text-success"></i>{{ __('account.personal_data') }}</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><small class="text-muted d-block">{{ __('account.name') }}</small><strong>{{ $user->nom_complet }}</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">{{ __('account.email') }}</small><strong>{{ $user->email }}</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">{{ __('account.phone') }}</small><strong>{{ $user->telephone ?: '—' }}</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">{{ __('account.role') }}</small><strong>{{ $user->role }}</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">{{ __('account.etablissement') }}</small><strong>{{ optional($user->etablissement)->nom ?: '—' }}</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">{{ __('account.last_login') }}</small><strong>{{ optional($user->last_login_at)?->format('d/m/Y H:i') ?: '—' }}</strong></div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-shield-check me-2 text-success"></i>{{ __('account.rights_title') }}</div>
            <div class="card-body">
                <p class="text-muted" style="font-size:.9rem;">{{ __('account.rights_intro') }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('account.export') }}" class="btn btn-primary">
                        <i class="bi bi-download me-2"></i>{{ __('account.export_btn') }}
                    </a>
                    <a href="{{ route('legal.privacy') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-file-text me-2"></i>{{ __('legal.nav_privacy') }}
                    </a>
                </div>
                <p class="text-muted mt-3 mb-0" style="font-size:.82rem;">
                    <i class="bi bi-info-circle me-1"></i>{{ __('account.erasure_notice') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
