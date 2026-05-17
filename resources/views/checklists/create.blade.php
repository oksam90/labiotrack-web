@extends('layouts.app')
@section('title', __('checklists.page_create_title'))
@section('content')
<div class="page-header">
    <h4 class="fw-bold mb-0"><i class="bi bi-check2-square me-2 text-success"></i>{{ __('checklists.header_create') }}</h4>
    <small class="text-muted">{{ __('checklists.subtitle_create') }}</small>
</div>
<div class="row justify-content-center">
<div class="col-md-7">
<div class="card">
<div class="card-body p-4">
<form method="POST" action="{{ route('checklists.store') }}">
@csrf
<div class="mb-3">
    <label class="form-label">{{ __('checklists.form_service') }}</label>
    <select name="service_id" class="form-select">
        <option value="">{{ __('checklists.form_service_general') }}</option>
        @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach
    </select>
</div>

<div class="mb-4">
    <label class="form-label fw-bold">{{ __('checklists.form_checkpoints') }}</label>
    @php
    $items = [
        'boites_fermees_75'           => ['label_key'=>'item_boites_label',   'desc_key'=>'item_boites_desc',   'icon'=>'📦'],
        'sacs_correctement_etiquetes' => ['label_key'=>'item_sacs_label',     'desc_key'=>'item_sacs_desc',     'icon'=>'🏷️'],
        'local_ventile'               => ['label_key'=>'item_local_label',    'desc_key'=>'item_local_desc',    'icon'=>'💨'],
        'epi_port'                    => ['label_key'=>'item_epi_label',      'desc_key'=>'item_epi_desc',      'icon'=>'🧤'],
        'sacs_noirs_non_contamines'   => ['label_key'=>'item_noirs_label',    'desc_key'=>'item_noirs_desc',    'icon'=>'⚫'],
        'contenants_integres'         => ['label_key'=>'item_integres_label', 'desc_key'=>'item_integres_desc', 'icon'=>'✅'],
    ];
    @endphp

    <div class="row g-2">
    @foreach($items as $key => $item)
    <div class="col-12">
        <div class="d-flex align-items-start gap-3 p-3 rounded border checklist-item" style="cursor:pointer;" data-target="{{ $key }}">
            <div style="font-size:1.5rem;">{{ $item['icon'] }}</div>
            <div class="flex-fill">
                <div class="fw-semibold">{{ __('checklists.' . $item['label_key']) }}</div>
                <small class="text-muted">{{ __('checklists.' . $item['desc_key']) }}</small>
            </div>
            <div class="form-check form-switch mt-1">
                <input class="form-check-input" type="checkbox" name="{{ $key }}" id="{{ $key }}" value="1" role="switch" style="width:2.5rem;height:1.3rem;" onchange="updateScore()">
            </div>
        </div>
    </div>
    @endforeach
    </div>
</div>

<div class="mb-3 p-3 rounded text-center" style="background:#f0fdf4;border:2px solid #1B6B3A;">
    <div style="font-size:.85rem;color:#1B6B3A;font-weight:600;">{{ __('checklists.score_computed') }}</div>
    <div id="score-display" style="font-size:2.5rem;font-weight:800;color:#1B6B3A;">0%</div>
    <div class="progress mt-1" style="height:8px;"><div id="score-bar" class="progress-bar bg-success" style="width:0%"></div></div>
</div>

<div class="mb-4">
    <label class="form-label">{{ __('checklists.form_observations') }}</label>
    <textarea name="observations" class="form-control" rows="3" placeholder="{{ __('checklists.form_observations_ph') }}"></textarea>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary flex-fill py-2"><i class="bi bi-save me-2"></i>{{ __('checklists.btn_save') }}</button>
    <a href="{{ route('checklists.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
</div>
</form>
</div>
</div>
</div>
</div>
@endsection
@push('scripts')
<script>
function updateScore() {
    const total = 6;
    const checked = document.querySelectorAll('input[type="checkbox"]:checked').length;
    const score = Math.round((checked / total) * 100);
    document.getElementById('score-display').textContent = score + '%';
    const bar = document.getElementById('score-bar');
    bar.style.width = score + '%';
    bar.className = 'progress-bar ' + (score >= 80 ? 'bg-success' : score >= 60 ? 'bg-warning' : 'bg-danger');
    document.getElementById('score-display').style.color = score >= 80 ? '#1B6B3A' : score >= 60 ? '#D4A017' : '#C0392B';
}

document.querySelectorAll('.checklist-item').forEach(item => {
    item.addEventListener('click', function(e) {
        if (e.target.type === 'checkbox') return;
        const targetId = this.dataset.target;
        const cb = document.getElementById(targetId);
        cb.checked = !cb.checked;
        updateScore();
    });
});
</script>
@endpush
