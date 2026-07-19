@extends('layouts.app')
@section('title', __('declarations.page_create_title'))
@section('content')
<div class="page-header">
    <h4 class="fw-bold mb-0"><i class="bi bi-clipboard-plus me-2 text-success"></i>{{ __('declarations.header_create') }}</h4>
    <small class="text-muted">{{ __('declarations.subtitle_create') }}</small>
</div>

<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card">
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('declarations.store') }}" enctype="multipart/form-data" id="decl-form" novalidate>
                    @csrf

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <label class="form-label mb-0 fw-bold">{{ __('declarations.lines_title') }} <span class="text-danger">*</span></label>
                            <small class="text-muted d-block">{{ __('declarations.lines_hint') }}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success" id="btn-add-line">
                            <i class="bi bi-plus-lg me-1"></i>{{ __('declarations.btn_add_line') }}
                        </button>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm align-middle" id="lignes-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width:180px;">{{ __('declarations.line_service') }}</th>
                                    <th style="min-width:200px;">{{ __('declarations.line_container') }}</th>
                                    <th style="width:120px;">{{ __('declarations.line_count') }}</th>
                                    <th class="text-end" style="width:110px;">{{ __('declarations.line_weight') }}</th>
                                    <th style="width:44px;"></th>
                                </tr>
                            </thead>
                            <tbody id="lignes-body"><!-- lignes injectées par JS --></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">{{ __('declarations.total_containers') }} :
                                        <span id="total-nb" class="text-success">0</span></th>
                                    <th class="text-end">{{ __('declarations.total_weight') }} :</th>
                                    <th class="text-end text-primary fw-bold"><span id="total-poids">0</span> kg</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('declarations.form_notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('declarations.form_notes_ph') }}">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">{{ __('declarations.form_photo') }}</label>
                        <input type="file" name="photo" class="form-control" accept="image/*" capture="environment">
                        <small class="text-muted">{{ __('declarations.form_photo_hint') }}</small>
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

{{-- Gabarit d'une ligne (cloné par JS) --}}
<template id="ligne-template">
    <tr class="ligne-row">
        <td>
            <select name="lignes[__I__][service_id]" class="form-select form-select-sm sel-service" required>
                <option value="">{{ __('declarations.line_select_ph') }}</option>
                @foreach($services as $s)
                <option value="{{ $s->id }}">{{ $s->nom }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="lignes[__I__][type_contenant_id]" class="form-select form-select-sm sel-contenant" required>
                <option value="" data-poids="0">{{ __('declarations.line_select_ph') }}</option>
                @foreach($typeContenants as $tc)
                <option value="{{ $tc->id }}" data-poids="{{ $tc->poids_moyen_kg }}">{{ $tc->nom }} (~{{ $tc->poids_moyen_kg }} {{ __('declarations.form_kg_per_unit') }})</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="lignes[__I__][nombre_contenants]" class="form-control form-control-sm text-center inp-nombre" value="1" min="1" max="999" required>
        </td>
        <td class="text-end"><span class="ligne-poids fw-semibold">0</span> kg</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-line" title="{{ __('declarations.btn_remove_line') }}"><i class="bi bi-trash"></i></button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
(function () {
    const body     = document.getElementById('lignes-body');
    const template = document.getElementById('ligne-template');
    let index = 0;

    function addLine() {
        const html = template.innerHTML.replace(/__I__/g, index);
        const tmp  = document.createElement('tbody');
        tmp.innerHTML = html.trim();
        const row = tmp.firstElementChild;
        body.appendChild(row);
        index++;
        wireRow(row);
        recalc();
    }

    function wireRow(row) {
        row.querySelector('.sel-contenant').addEventListener('change', recalc);
        row.querySelector('.inp-nombre').addEventListener('input', recalc);
        row.querySelector('.btn-remove-line').addEventListener('click', function () {
            // Toujours conserver au moins une ligne.
            if (body.querySelectorAll('.ligne-row').length > 1) {
                row.remove();
            } else {
                row.querySelector('.sel-service').value = '';
                row.querySelector('.sel-contenant').value = '';
                row.querySelector('.inp-nombre').value = 1;
            }
            recalc();
        });
    }

    function recalc() {
        let totalNb = 0, totalPoids = 0;
        body.querySelectorAll('.ligne-row').forEach(function (row) {
            const sel   = row.querySelector('.sel-contenant');
            const opt   = sel.options[sel.selectedIndex];
            const poids = opt ? parseFloat(opt.dataset.poids || 0) : 0;
            const nb    = parseInt(row.querySelector('.inp-nombre').value) || 0;
            const pl    = poids * nb;
            row.querySelector('.ligne-poids').textContent = pl.toFixed(2);
            totalNb    += nb;
            totalPoids += pl;
        });
        document.getElementById('total-nb').textContent    = totalNb;
        document.getElementById('total-poids').textContent = totalPoids.toFixed(2);
    }

    document.getElementById('btn-add-line').addEventListener('click', addLine);

    // ── Validation métier côté client (avant POST) ──────────────
    function showError(msg) {
        let err = document.getElementById('lignes-error');
        if (err) err.remove();
        const form = document.getElementById('decl-form');
        const div = document.createElement('div');
        div.id = 'lignes-error';
        div.className = 'alert alert-danger py-2';
        div.textContent = msg;
        form.prepend(div);
        div.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function validateLignes() {
        const old = document.getElementById('lignes-error');
        if (old) old.remove();
        let complete = 0, incomplete = false;
        body.querySelectorAll('.ligne-row').forEach(function (row) {
            const svc = row.querySelector('.sel-service').value;
            const tc  = row.querySelector('.sel-contenant').value;
            const nb  = parseInt(row.querySelector('.inp-nombre').value) || 0;
            const filled = (svc ? 1 : 0) + (tc ? 1 : 0) + (nb >= 1 ? 1 : 0);
            if (filled === 3) complete++;
            else if (filled > 0) incomplete = true;
        });
        if (incomplete) { showError(@json(__('declarations.error_incomplete_line'))); return false; }
        if (complete === 0) { showError(@json(__('declarations.error_min_one_line'))); return false; }
        return true;
    }
    document.getElementById('decl-form').addEventListener('submit', function (e) {
        if (!validateLignes()) e.preventDefault();
    });

    // Première ligne au chargement
    addLine();
})();
</script>
@endpush
