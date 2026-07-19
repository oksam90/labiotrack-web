@extends('layouts.app')

@section('title', __('signatures.page_sign_title') . ' ' . $collecte->numero_bordereau)

@push('styles')
<style>
    .signature-container { max-width: 800px; margin: 0 auto; }
    .signature-pad-wrapper {
        border: 2px dashed #1B6B3A;
        border-radius: 6px;
        background: #fff;
        position: relative;
        padding: 0;
    }
    #signature-canvas {
        width: 100%;
        height: 280px;
        display: block;
        touch-action: none;
        cursor: crosshair;
    }
    .sig-empty-hint {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #c5d3c8;
        font-size: 1rem;
        pointer-events: none;
    }
    .sig-empty-hint.hidden { display: none; }
    .collecte-resume {
        background: #f0fdf4;
        border-left: 4px solid #1B6B3A;
        padding: 12px 16px;
        border-radius: 4px;
    }
    .collecte-resume p { margin: 2px 0; }
    .checkbox-confirm {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        background: #fff8e1;
        border: 1px solid #f9d77e;
        border-radius: 4px;
        padding: 10px 14px;
    }
</style>
@endpush

@section('content')
<div class="signature-container">

    <h2 class="mb-3">
        <i class="bi bi-pen"></i> {{ __('signatures.header_sign') }}
    </h2>

    {{-- ── Résumé collecte (lecture seule) ─────────────────── --}}
    <div class="collecte-resume mb-3">
        <p><strong>{{ __('signatures.lbl_bordereau') }}</strong> {{ $collecte->numero_bordereau }}</p>
        <p><strong>{{ __('signatures.lbl_etab') }}</strong> {{ $etablissement->nom ?? '—' }}</p>
        <p><strong>{{ __('signatures.lbl_date_collecte') }}</strong> {{ $collecte->date_collecte?->format('d/m/Y H:i') ?? '—' }}</p>
        <p><strong>{{ __('signatures.lbl_agent_collecteur') }}</strong>
            {{ $collecte->collecteur ? $collecte->collecteur->prenom . ' ' . $collecte->collecteur->nom : '—' }}
        </p>
        <p><strong>{{ __('signatures.lbl_containers') }}</strong> {{ $collecte->nombre_contenants }} —
           <strong>{{ __('signatures.lbl_weight_declared') }}</strong> {{ number_format($collecte->poids_declare_kg ?? 0, 2) }} kg</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form id="signature-form" method="POST"
          action="{{ route('signatures.store', $collecte->id) }}"
          data-msg-empty="{{ __('signatures.js_empty_signature') }}"
          data-msg-legal="{{ __('signatures.js_confirm_legal') }}"
          data-msg-submitting="{{ __('signatures.btn_submitting') }}">
        @csrf

        {{-- ── Zone de dessin ─────────────────────────────── --}}
        <label class="form-label fw-bold mt-3">{{ __('signatures.canvas_label') }}</label>
        <div class="signature-pad-wrapper mb-2">
            <canvas id="signature-canvas"></canvas>
            <div id="sig-hint" class="sig-empty-hint">
                <i class="bi bi-pencil-square me-2"></i> {{ __('signatures.canvas_hint') }}
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="btn-clear">
            <i class="bi bi-eraser"></i> {{ __('signatures.btn_clear') }}
        </button>

        {{-- ── Identité signataire ────────────────────────── --}}
        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('signatures.form_signer_name') }}</label>
                <input type="text" name="signataire_nom" class="form-control"
                       value="{{ trim($user->prenom . ' ' . $user->nom) }}"
                       placeholder="{{ __('signatures.form_signer_name_ph') }}" required maxlength="255" />
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('signatures.form_signer_function') }}</label>
                <input type="text" name="signataire_fonction" class="form-control"
                       placeholder="{{ __('signatures.form_signer_function_ph') }}" maxlength="255" />
            </div>
        </div>

        {{-- ── Mention légale ─────────────────────────────── --}}
        <label class="form-label">{{ __('signatures.form_legal_mention') }}</label>
        <textarea name="commentaire" class="form-control mb-2" rows="2"
                  required maxlength="500">{{ __('signatures.form_legal_default') }}</textarea>

        <label class="checkbox-confirm mb-3">
            <input type="checkbox" id="chk-approve" required />
            <span>{{ __('signatures.form_legal_checkbox') }}</span>
        </label>

        {{-- Hidden : payload signature + screen --}}
        <input type="hidden" name="signature_image" id="signature_image" />
        <input type="hidden" name="screen_resolution" id="screen_resolution" />

        <div class="d-flex gap-2">
            <a href="{{ route('collectes.show', $collecte->id) }}" class="btn btn-light">
                <i class="bi bi-x"></i> {{ __('signatures.btn_cancel') }}
            </a>
            <button type="submit" class="btn btn-success" id="btn-submit" disabled>
                <i class="bi bi-check-circle"></i> {{ __('signatures.btn_submit') }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/js/signature_pad.umd.min.js') }}"></script>
<script>
(function () {
    const canvas       = document.getElementById('signature-canvas');
    const hint         = document.getElementById('sig-hint');
    const chk          = document.getElementById('chk-approve');
    const btnSubmit    = document.getElementById('btn-submit');
    const btnClear     = document.getElementById('btn-clear');
    const form         = document.getElementById('signature-form');
    const fieldImage   = document.getElementById('signature_image');
    const fieldScreen  = document.getElementById('screen_resolution');

    const pad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)',
        minWidth: 1,
        maxWidth: 3,
    });

    // Responsive : adapter le canvas à la largeur réelle
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        pad.clear();
        hint.classList.remove('hidden');
        updateState();
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    // Cache le hint dès le premier coup de stylet
    pad.addEventListener('beginStroke', () => hint.classList.add('hidden'));
    pad.addEventListener('endStroke', updateState);

    btnClear.addEventListener('click', () => {
        pad.clear();
        hint.classList.remove('hidden');
        updateState();
    });

    chk.addEventListener('change', updateState);

    function updateState() {
        btnSubmit.disabled = pad.isEmpty() || !chk.checked;
    }

    // Soumission : injecte image base64 + résolution écran
    // Les messages d'erreur viennent des data-attributes du form pour
    // suivre la locale active (i18n via __() côté Blade).
    form.addEventListener('submit', function (e) {
        if (pad.isEmpty()) {
            e.preventDefault();
            alert(form.dataset.msgEmpty || 'Please draw your signature.');
            return;
        }
        if (!chk.checked) {
            e.preventDefault();
            alert(form.dataset.msgLegal || 'Please confirm the legal statement.');
            return;
        }
        fieldImage.value  = pad.toDataURL('image/png');
        fieldScreen.value = `${screen.width}x${screen.height}`;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + (form.dataset.msgSubmitting || 'Saving…');
    });
})();
</script>
@endpush
