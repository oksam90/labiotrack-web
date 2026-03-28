@extends('layouts.app')
@section('title','Nouvelle Checklist')
@section('content')
<div class="page-header">
    <h4 class="fw-bold mb-0"><i class="bi bi-check2-square me-2 text-success"></i>Checklist de conformité</h4>
    <small class="text-muted">Évaluation des pratiques — Score calculé automatiquement</small>
</div>
<div class="row justify-content-center">
<div class="col-md-7">
<div class="card">
<div class="card-body p-4">
<form method="POST" action="{{ route('checklists.store') }}">
@csrf
<div class="mb-3">
    <label class="form-label">Service (optionnel)</label>
    <select name="service_id" class="form-select">
        <option value="">— Général (tout l'établissement) —</option>
        @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach
    </select>
</div>

<div class="mb-4">
    <label class="form-label fw-bold">Points de contrôle</label>
    @php
    $items = [
        'boites_fermees_75' => ['label'=>'Boîtes de sécurité fermées aux 3/4', 'desc'=>'Toutes les boîtes piquants-coupants sont correctement fermées avant remplissage complet', 'icon'=>'📦'],
        'sacs_correctement_etiquetes' => ['label'=>'Sacs correctement étiquetés', 'desc'=>'Les sacs jaunes portent les mentions réglementaires obligatoires', 'icon'=>'🏷️'],
        'local_ventile' => ['label'=>'Local de stockage ventilé', 'desc'=>'Le local central dispose d\'une ventilation adéquate et est maintenu propre', 'icon'=>'💨'],
        'epi_port' => ['label'=>'Port des EPI respecté', 'desc'=>'Le personnel porte les équipements de protection individuelle requis', 'icon'=>'🧤'],
        'sacs_noirs_non_contamines' => ['label'=>'Sacs noirs non contaminés', 'desc'=>'Aucun déchet DASRI n\'est mélangé dans les sacs noirs assimilés ménagers', 'icon'=>'⚫'],
        'contenants_integres' => ['label'=>'Contenants intègres (non percés)', 'desc'=>'Tous les contenants sont en bon état, sans fuite ni perforation', 'icon'=>'✅'],
    ];
    @endphp

    <div class="row g-2">
    @foreach($items as $key => $item)
    <div class="col-12">
        <div class="d-flex align-items-start gap-3 p-3 rounded border checklist-item" style="cursor:pointer;" data-target="{{ $key }}">
            <div style="font-size:1.5rem;">{{ $item['icon'] }}</div>
            <div class="flex-fill">
                <div class="fw-semibold">{{ $item['label'] }}</div>
                <small class="text-muted">{{ $item['desc'] }}</small>
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
    <div style="font-size:.85rem;color:#1B6B3A;font-weight:600;">Score de conformité calculé</div>
    <div id="score-display" style="font-size:2.5rem;font-weight:800;color:#1B6B3A;">0%</div>
    <div class="progress mt-1" style="height:8px;"><div id="score-bar" class="progress-bar bg-success" style="width:0%"></div></div>
</div>

<div class="mb-4">
    <label class="form-label">Observations (optionnel)</label>
    <textarea name="observations" class="form-control" rows="3" placeholder="Notes, actions correctives prévues..."></textarea>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary flex-fill py-2"><i class="bi bi-save me-2"></i>Enregistrer la checklist</button>
    <a href="{{ route('checklists.index') }}" class="btn btn-outline-secondary">Annuler</a>
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

// ✅ CORRIGÉ : on écoute le clic sur la zone, mais on ignore si la cible est déjà l'input
// pour éviter le double-toggle (clic sur le switch = l'input est togglé par le navigateur,
// puis l'écouteur le retogglait une 2e fois → restait bloqué)
document.querySelectorAll('.checklist-item').forEach(item => {
    item.addEventListener('click', function(e) {
        // Si le clic vient directement du checkbox, ne rien faire (le navigateur l'a déjà géré)
        if (e.target.type === 'checkbox') return;
        const targetId = this.dataset.target;
        const cb = document.getElementById(targetId);
        cb.checked = !cb.checked;
        updateScore();
    });
});
</script>
@endpush
