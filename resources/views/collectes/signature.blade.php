@extends('layouts.app')

@section('title', 'Signature bordereau ' . $collecte->numero_bordereau)

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
        <i class="bi bi-pen"></i> Signature du bordereau de collecte
    </h2>

    {{-- ── Résumé collecte (lecture seule) ─────────────────── --}}
    <div class="collecte-resume mb-3">
        <p><strong>Bordereau :</strong> {{ $collecte->numero_bordereau }}</p>
        <p><strong>Établissement :</strong> {{ $etablissement->nom ?? '—' }}</p>
        <p><strong>Date collecte :</strong> {{ $collecte->date_collecte?->format('d/m/Y H:i') ?? '—' }}</p>
        <p><strong>Agent collecteur :</strong>
            {{ $collecte->collecteur ? $collecte->collecteur->prenom . ' ' . $collecte->collecteur->nom : '—' }}
        </p>
        <p><strong>Contenants :</strong> {{ $collecte->nombre_contenants }} —
           <strong>Poids déclaré :</strong> {{ number_format($collecte->poids_declare_kg ?? 0, 2) }} kg</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form id="signature-form" method="POST"
          action="{{ route('signatures.store', $collecte->id) }}">
        @csrf

        {{-- ── Zone de dessin ─────────────────────────────── --}}
        <label class="form-label fw-bold mt-3">Signature du client</label>
        <div class="signature-pad-wrapper mb-2">
            <canvas id="signature-canvas"></canvas>
            <div id="sig-hint" class="sig-empty-hint">
                <i class="bi bi-pencil-square me-2"></i> Dessinez votre signature ici
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="btn-clear">
            <i class="bi bi-eraser"></i> Effacer
        </button>

        {{-- ── Identité signataire ────────────────────────── --}}
        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <label class="form-label">Nom complet du signataire *</label>
                <input type="text" name="signataire_nom" class="form-control"
                       value="{{ trim($user->prenom . ' ' . $user->nom) }}"
                       placeholder="Nom Prénom" required maxlength="255" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Fonction</label>
                <input type="text" name="signataire_fonction" class="form-control"
                       placeholder="Ex: Responsable QHSE" maxlength="255" />
            </div>
        </div>

        {{-- ── Mention légale ─────────────────────────────── --}}
        <label class="form-label">Mention légale *</label>
        <textarea name="commentaire" class="form-control mb-2" rows="2"
                  required maxlength="500">Lu et Approuvé</textarea>

        <label class="checkbox-confirm mb-3">
            <input type="checkbox" id="chk-approve" required />
            <span>
                Je certifie avoir pris connaissance des informations de collecte
                ci-dessus et confirme leur exactitude. Cette signature électronique
                a la même valeur juridique qu'une signature manuscrite.
            </span>
        </label>

        {{-- Hidden : payload signature + screen --}}
        <input type="hidden" name="signature_image" id="signature_image" />
        <input type="hidden" name="screen_resolution" id="screen_resolution" />

        <div class="d-flex gap-2">
            <a href="{{ route('collectes.show', $collecte->id) }}" class="btn btn-light">
                <i class="bi bi-x"></i> Annuler
            </a>
            <button type="submit" class="btn btn-success" id="btn-submit" disabled>
                <i class="bi bi-check-circle"></i> Valider la signature
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js"></script>
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
    form.addEventListener('submit', function (e) {
        if (pad.isEmpty()) {
            e.preventDefault();
            alert('Veuillez dessiner votre signature.');
            return;
        }
        if (!chk.checked) {
            e.preventDefault();
            alert('Veuillez confirmer la mention légale.');
            return;
        }
        fieldImage.value  = pad.toDataURL('image/png');
        fieldScreen.value = `${screen.width}x${screen.height}`;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement…';
    });
})();
</script>
@endpush
