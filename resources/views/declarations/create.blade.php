@extends('layouts.app')
@section('title', __('declarations.page_create_title'))
@section('content')
<div class="page-header">
    <h4 class="fw-bold mb-0"><i class="bi bi-clipboard-plus me-2 text-success"></i>{{ __('declarations.header_create') }}</h4>
    <small class="text-muted">{{ __('declarations.subtitle_create') }}</small>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <!-- Indicateur étapes -->
        <div class="d-flex align-items-center gap-2 mb-4">
            <div class="d-flex align-items-center gap-1">
                <div style="width:28px;height:28px;border-radius:50%;background:#1B6B3A;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;">1</div>
                <span style="font-size:.82rem;font-weight:600;color:#1B6B3A;">{{ __('declarations.step_service') }}</span>
            </div>
            <div style="flex:1;height:2px;background:#e5e9ef;"></div>
            <div class="d-flex align-items-center gap-1">
                <div style="width:28px;height:28px;border-radius:50%;background:#e5e9ef;color:#9ca3af;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;">2</div>
                <span style="font-size:.82rem;color:#9ca3af;">{{ __('declarations.step_container') }}</span>
            </div>
            <div style="flex:1;height:2px;background:#e5e9ef;"></div>
            <div class="d-flex align-items-center gap-1">
                <div style="width:28px;height:28px;border-radius:50%;background:#e5e9ef;color:#9ca3af;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;">3</div>
                <span style="font-size:.82rem;color:#9ca3af;">{{ __('declarations.step_quantity') }}</span>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('declarations.store') }}" enctype="multipart/form-data"
                      data-i18n-estimated="{{ __('declarations.summary_estimated') }}">
                    @csrf

                    <!-- ÉTAPE 1 : SERVICE -->
                    <div class="mb-4">
                        <label class="form-label">
                            <span class="badge bg-success me-1">1</span> {{ __('declarations.form_service_label') }} <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2" id="services-grid">
                            @foreach($services as $s)
                            <div class="col-6 col-md-4">
                                <input type="radio" name="service_id" value="{{ $s->id }}" id="svc_{{ $s->id }}" class="d-none service-radio" {{ old('service_id')==$s->id ? 'checked' : '' }} required>
                                <label for="svc_{{ $s->id }}" class="service-btn w-100 text-center p-3 rounded border cursor-pointer" style="cursor:pointer;transition:.15s;">
                                    <i class="bi bi-hospital d-block mb-1 fs-5"></i>
                                    <small>{{ $s->nom }}</small>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- ÉTAPE 2 : TYPE CONTENANT -->
                    <div class="mb-4">
                        <label class="form-label">
                            <span class="badge bg-success me-1">2</span> {{ __('declarations.form_container_type') }} <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            @foreach($typeContenants as $tc)
                            <div class="col-6 col-md-4">
                                <input type="radio" name="type_contenant_id" value="{{ $tc->id }}" id="tc_{{ $tc->id }}" class="d-none contenant-radio" data-poids="{{ $tc->poids_moyen_kg }}" {{ old('type_contenant_id')==$tc->id ? 'checked' : '' }} required>
                                <label for="tc_{{ $tc->id }}" class="contenant-btn w-100 p-2 rounded border text-center" style="cursor:pointer;font-size:.82rem;transition:.15s;">
                                    @php
                                        $emoji = str_contains(strtolower($tc->nom),'boite')||str_contains(strtolower($tc->nom),'box') ? '📦' : (str_contains(strtolower($tc->nom),'jaune') ? '🟡' : (str_contains(strtolower($tc->nom),'noir') ? '⚫' : '🔴'));
                                    @endphp
                                    <span class="d-block fs-4">{{ $emoji }}</span>
                                    <strong>{{ $tc->nom }}</strong><br>
                                    <span class="text-muted">~{{ $tc->poids_moyen_kg }} {{ __('declarations.form_kg_per_unit') }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- ÉTAPE 3 : QUANTITÉ -->
                    <div class="mb-4">
                        <label class="form-label">
                            <span class="badge bg-success me-1">3</span> {{ __('declarations.form_container_full_count') }} <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex align-items-center gap-3">
                            <button type="button" class="btn btn-outline-secondary px-3" id="btn-moins">-</button>
                            <input type="number" name="nombre_contenants" id="nombre_contenants" class="form-control text-center fw-bold fs-4" value="{{ old('nombre_contenants', 1) }}" min="1" max="999" required style="max-width:120px;">
                            <button type="button" class="btn btn-outline-secondary px-3" id="btn-plus">+</button>
                            <div class="text-muted" id="poids-preview" style="font-size:.88rem;"></div>
                        </div>
                    </div>

                    <!-- NOTES & PHOTO (optionnel) -->
                    <div class="mb-3">
                        <label class="form-label">{{ __('declarations.form_notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('declarations.form_notes_ph') }}">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">{{ __('declarations.form_photo') }}</label>
                        <input type="file" name="photo" class="form-control" accept="image/*" capture="environment">
                        <small class="text-muted">{{ __('declarations.form_photo_hint') }}</small>
                    </div>

                    <!-- RÉSUMÉ AUTOMATIQUE -->
                    <div class="p-3 rounded mb-4" style="background:#f0fdf4;border:2px solid #bbf7d0;" id="summary" style="display:none;">
                        <div class="fw-bold mb-1 text-success"><i class="bi bi-check-circle me-1"></i>{{ __('declarations.summary_title') }}</div>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div style="font-size:.75rem;color:#6b7280;">{{ __('declarations.summary_service') }}</div>
                                <div id="sum-service" class="fw-semibold" style="font-size:.88rem;">—</div>
                            </div>
                            <div class="col-4">
                                <div style="font-size:.75rem;color:#6b7280;">{{ __('declarations.summary_containers') }}</div>
                                <div id="sum-nb" class="fw-bold text-success">0</div>
                            </div>
                            <div class="col-4">
                                <div style="font-size:.75rem;color:#6b7280;">{{ __('declarations.summary_weight') }}</div>
                                <div id="sum-poids" class="fw-bold text-primary">0 kg</div>
                            </div>
                        </div>
                        <div class="mt-2" style="font-size:.75rem;color:#6b7280;">
                            <i class="bi bi-clock me-1"></i>{{ __('declarations.summary_timestamp') }} {{ now()->format('d/m/Y H:i') }}
                            &nbsp;&nbsp;<i class="bi bi-qr-code me-1"></i>{{ __('declarations.summary_qr_auto') }}
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill py-2">
                            <i class="bi bi-check-circle me-2"></i>{{ __('declarations.btn_save') }}
                        </button>
                        <a href="{{ route('declarations.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedPoids = 0;
let selectedService = '';
const i18nEstimated = document.querySelector('form[data-i18n-estimated]')?.dataset.i18nEstimated || 'estimated';

// Sélection service
document.querySelectorAll('.service-radio').forEach(r => {
    r.addEventListener('change', function() {
        document.querySelectorAll('.service-btn').forEach(b => b.style.cssText = '');
        this.nextElementSibling.style.cssText = 'background:#d1fae5;border-color:#1B6B3A!important;font-weight:600;';
        selectedService = this.nextElementSibling.textContent.trim();
        document.getElementById('sum-service').textContent = selectedService;
        updateSummary();
    });
});

// Sélection contenant
document.querySelectorAll('.contenant-radio').forEach(r => {
    r.addEventListener('change', function() {
        document.querySelectorAll('.contenant-btn').forEach(b => b.style.cssText = '');
        this.nextElementSibling.style.cssText = 'background:#dbeafe;border-color:#2980B9!important;';
        selectedPoids = parseFloat(this.dataset.poids);
        updateSummary();
    });
});

// Quantité
document.getElementById('btn-moins').addEventListener('click', () => {
    const inp = document.getElementById('nombre_contenants');
    if (inp.value > 1) { inp.value--; updateSummary(); }
});
document.getElementById('btn-plus').addEventListener('click', () => {
    const inp = document.getElementById('nombre_contenants');
    inp.value++; updateSummary();
});
document.getElementById('nombre_contenants').addEventListener('input', updateSummary);

function updateSummary() {
    const nb = parseInt(document.getElementById('nombre_contenants').value) || 0;
    const poids = (selectedPoids * nb).toFixed(2);
    document.getElementById('sum-nb').textContent = nb;
    document.getElementById('sum-poids').textContent = poids + ' kg';
    document.getElementById('poids-preview').textContent = selectedPoids ? `≈ ${poids} kg ${i18nEstimated}` : '';
    document.getElementById('summary').style.display = (nb > 0 && selectedPoids) ? 'block' : 'none';
}
</script>
@endpush
