@php
    $role = Auth::user()->role;
    // Route de l'action principale selon le rôle (fallback dashboard).
    $ctaRoutes = [
        'agent'             => 'declarations.create',
        'qhse'              => 'dashboard',
        'collecteur'        => 'collectes.index',
        'prestataire'       => 'collectes.index',
        'client_signataire' => 'collectes.index',
        'admin'             => 'admin.index',
        'admin_reseau'      => 'superadmin.index',
        'superadmin'        => 'superadmin.index',
    ];
    $hasMessage = \Illuminate\Support\Facades\Lang::has('onboarding.' . $role);
    $ctaRoute   = $ctaRoutes[$role] ?? 'dashboard';
@endphp

@if($hasMessage && \Illuminate\Support\Facades\Route::has($ctaRoute))
<div id="lbt-onboarding"
     data-user="{{ Auth::id() }}"
     class="card mb-3"
     style="display:none;border-left:4px solid var(--secondary);background:#fffdf5;">
    <div class="card-body d-flex align-items-start gap-3 py-3">
        <i class="bi bi-stars fs-4" style="color:var(--secondary);"></i>
        <div class="flex-fill">
            <div class="fw-semibold mb-1">{{ __('onboarding.welcome', ['name' => Auth::user()->prenom]) }}</div>
            <div class="text-muted" style="font-size:.9rem;">{{ __('onboarding.' . $role) }}</div>
            <a href="{{ route($ctaRoute) }}" class="btn btn-sm btn-primary mt-2">
                {{ __('onboarding.' . $role . '_cta') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <button type="button" id="lbt-onboarding-dismiss" class="btn-close" aria-label="{{ __('onboarding.dismiss') }}"></button>
    </div>
</div>
<script>
    (function () {
        try {
            var el = document.getElementById('lbt-onboarding');
            if (!el) return;
            var key = 'lbt_onboard_ack_' + el.dataset.user;
            if (!localStorage.getItem(key)) { el.style.display = 'block'; }
            document.getElementById('lbt-onboarding-dismiss').addEventListener('click', function () {
                localStorage.setItem(key, '1');
                el.style.display = 'none';
            });
        } catch (e) { /* localStorage indisponible → on n'affiche pas */ }
    })();
</script>
@endif
