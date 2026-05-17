@extends('layouts.app')
@section('title', __('admin.page_activites_title'))
@push('styles')
<style>
.pulse-dot {
    width:10px; height:10px; border-radius:50%; background:#16a34a; display:inline-block;
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%,100% { opacity:1; transform:scale(1); }
    50%      { opacity:.5; transform:scale(1.3); }
}
.flux-item {
    border-left:3px solid #e5e9ef; padding:.7rem 1rem;
    border-radius:0 8px 8px 0; background:#fff; margin-bottom:.5rem;
    transition: all .3s;
    animation: slideIn .4s ease;
}
@keyframes slideIn {
    from { opacity:0; transform:translateX(-15px); }
    to   { opacity:1; transform:translateX(0); }
}
.flux-item.info      { border-left-color:#2980B9; }
.flux-item.success   { border-left-color:#16a34a; }
.flux-item.warning   { border-left-color:#D4A017; }
.flux-item.danger    { border-left-color:#C0392B; }
.flux-item.secondary { border-left-color:#6b7280; }
.type-icon { width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0; }
.live-stat { background:#fff;border-radius:12px;border:1px solid #e5e9ef;padding:1rem 1.2rem;text-align:center; }
.live-stat .val { font-size:1.8rem;font-weight:800;color:#1B6B3A; }
.live-stat .lbl { font-size:.75rem;color:#6b7280; }
</style>
@endpush
@section('content')

<div class="page-header d-flex align-items-center justify-content-between"
     data-route="{{ route('admin.activites.data') }}"
     data-locale="{{ str_replace('_','-', app()->getLocale()) }}"
     data-msg-update="{{ __('admin.last_update', ['time' => '__TIME__']) }}"
     data-msg-next="{{ __('admin.next_refresh', ['s' => '__S__']) }}"
     data-msg-manual="{{ __('admin.next_refresh_manual') }}"
     data-msg-empty="{{ __('admin.feed_empty') }}"
     data-msg-events="{{ __('admin.badge_events') }}"
     data-msg-ago-s="{{ __('admin.time_ago_seconds') }}"
     data-msg-ago-m="{{ __('admin.time_ago_minutes') }}"
     data-msg-ago-h="{{ __('admin.time_ago_hours') }}"
     data-type-decl="{{ __('admin.type_label_declaration') }}"
     data-type-coll="{{ __('admin.type_label_collecte') }}"
     data-type-check="{{ __('admin.type_label_checklist') }}"
     data-type-alert="{{ __('admin.type_label_alerte') }}"
     data-type-destr="{{ __('admin.type_label_destruction') }}"
     id="activites-meta">
    <div class="d-flex align-items-center gap-2">
        <h4 class="fw-bold mb-0"><i class="bi bi-activity me-2 text-success"></i>{{ __('admin.header_activites') }}</h4>
        <span class="pulse-dot"></span>
        <small class="text-muted">{{ __('admin.realtime') }}</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span id="last-update" class="text-muted" style="font-size:.8rem;"></span>
        <button class="btn btn-sm btn-outline-success" onclick="chargerActivites()">
            <i class="bi bi-arrow-clockwise me-1"></i>{{ __('admin.btn_refresh') }}
        </button>
    </div>
</div>

{{-- Stats live --}}
<div class="row g-3 mb-4" id="live-stats">
    <div class="col-6 col-md-2">
        <div class="live-stat">
            <div class="val" id="stat-declarations">{{ $stats['declarations_today'] }}</div>
            <div class="lbl"><i class="bi bi-clipboard-plus me-1"></i>{{ __('admin.stat_decl_today') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="live-stat">
            <div class="val" id="stat-collectes">{{ $stats['collectes_today'] }}</div>
            <div class="lbl"><i class="bi bi-truck me-1"></i>{{ __('admin.stat_collectes_today') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="live-stat">
            <div class="val" id="stat-checklists">{{ $stats['checklists_today'] }}</div>
            <div class="lbl"><i class="bi bi-check2-square me-1"></i>{{ __('admin.stat_checklists_today') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="live-stat">
            <div class="val text-danger" id="stat-alertes">{{ $stats['alertes_nonlues'] }}</div>
            <div class="lbl"><i class="bi bi-bell me-1"></i>{{ __('admin.stat_unread_alerts') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="live-stat">
            <div class="val text-info" id="stat-users">—</div>
            <div class="lbl"><i class="bi bi-people me-1"></i>{{ __('admin.stat_active_users') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="live-stat">
            <div class="val text-secondary">{{ $stats['etablissements'] }}</div>
            <div class="lbl"><i class="bi bi-hospital me-1"></i>{{ __('admin.stat_etabs_active') }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Flux d'activité --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div><i class="bi bi-list-ul me-2 text-success"></i>{{ __('admin.card_activity_feed') }}</div>
                <div id="flux-count" class="badge" style="background:#1B6B3A;">—</div>
            </div>
            <div class="card-body" style="max-height:520px;overflow-y:auto;" id="flux-container">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border text-success mb-2" role="status"></div>
                    <div>{{ __('admin.feed_loading') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphique activité --}}
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-pie-chart me-2 text-success"></i>{{ __('admin.card_dist_by_type') }}</div>
            <div class="card-body">
                <canvas id="typeChart" style="max-height:200px;"></canvas>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history me-2 text-success"></i>{{ __('admin.card_auto_refresh') }}</div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span style="font-size:.85rem;">{{ __('admin.refresh_interval') }}</span>
                    <select id="refresh-interval" class="form-select form-select-sm" style="width:auto;" onchange="changerIntervalle()">
                        <option value="15000">{{ __('admin.refresh_15s') }}</option>
                        <option value="30000" selected>{{ __('admin.refresh_30s') }}</option>
                        <option value="60000">{{ __('admin.refresh_1min') }}</option>
                        <option value="0">{{ __('admin.refresh_manual') }}</option>
                    </select>
                </div>
                <div class="progress" style="height:6px;">
                    <div id="refresh-bar" class="progress-bar bg-success" style="width:100%;transition:width linear 30s;"></div>
                </div>
                <small class="text-muted d-block mt-1" id="next-refresh">{{ __('admin.next_refresh', ['s' => 30]) }}</small>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
const META = document.getElementById('activites-meta').dataset;
let refreshTimer = null;
let chartInstance = null;
let intervalMs = 30000;
let countdown = 30;
let countdownTimer = null;

const icons = {
    declaration: {icon:'bi-clipboard-plus', bg:'#dbeafe', color:'#1e40af'},
    collecte:    {icon:'bi-truck',          bg:'#d1fae5', color:'#065f46'},
    checklist:   {icon:'bi-check2-square', bg:'#fef9c3', color:'#92400e'},
    alerte:      {icon:'bi-bell-fill',     bg:'#fee2e2', color:'#991b1b'},
    destruction: {icon:'bi-fire',          bg:'#f3f4f6', color:'#374151'},
};

const typeLabels = {
    declaration: META.typeDecl,
    collecte:    META.typeColl,
    checklist:   META.typeCheck,
    alerte:      META.typeAlert,
    destruction: META.typeDestr,
};

function formatDate(d) {
    const dt = new Date(d);
    return dt.toLocaleDateString(META.locale) + ' ' + dt.toLocaleTimeString(META.locale, {hour:'2-digit',minute:'2-digit'});
}

function timeAgo(d) {
    const diff = Math.floor((Date.now() - new Date(d)) / 1000);
    if(diff < 60)   return META.msgAgoS.replace(':n', diff);
    if(diff < 3600) return META.msgAgoM.replace(':n', Math.floor(diff/60));
    if(diff < 86400) return META.msgAgoH.replace(':n', Math.floor(diff/3600));
    return formatDate(d);
}

async function chargerActivites() {
    try {
        const resp = await fetch(META.route);
        const data = await resp.json();

        // Mise à jour stats
        document.getElementById('stat-declarations').textContent = data.stats.declarations_today;
        document.getElementById('stat-collectes').textContent    = data.stats.collectes_today;
        document.getElementById('stat-checklists').textContent   = data.stats.checklists_today;
        document.getElementById('stat-alertes').textContent      = data.stats.alertes_nonlues;
        document.getElementById('stat-users').textContent        = data.stats.users_connectes;
        document.getElementById('last-update').textContent       = META.msgUpdate.replace('__TIME__', new Date().toLocaleTimeString(META.locale));

        // Flux
        const container = document.getElementById('flux-container');
        document.getElementById('flux-count').textContent = data.flux.length + ' ' + META.msgEvents;

        if(data.flux.length === 0) {
            container.innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>' + META.msgEmpty + '</div>';
            return;
        }

        container.innerHTML = data.flux.map(item => {
            const cfg = icons[item.type] || icons.declaration;
            return `<div class="flux-item ${item.niveau}">
                <div class="d-flex align-items-start gap-2">
                    <div class="type-icon" style="background:${cfg.bg};">
                        <i class="bi ${cfg.icon}" style="color:${cfg.color};"></i>
                    </div>
                    <div class="flex-fill">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="fw-semibold" style="font-size:.88rem;">${item.description}</span>
                                <div style="font-size:.78rem;color:#6b7280;margin-top:.1rem;">
                                    <i class="bi bi-person me-1"></i>${item.acteur}
                                    <span class="mx-2">·</span>
                                    <i class="bi bi-hospital me-1"></i>${item.etablissement}
                                </div>
                            </div>
                            <small class="text-muted text-nowrap ms-2" style="font-size:.75rem;">${timeAgo(item.moment)}</small>
                        </div>
                    </div>
                </div>
            </div>`;
        }).join('');

        // Graphique répartition
        const counts = {};
        data.flux.forEach(f => { counts[f.type] = (counts[f.type]||0)+1; });
        const labels = Object.keys(counts).map(k => typeLabels[k] || k);
        const vals   = Object.values(counts);
        const colors = ['#2980B9','#16a34a','#D4A017','#C0392B','#6b7280'];

        if(chartInstance) chartInstance.destroy();
        const ctx = document.getElementById('typeChart');
        chartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: { labels, datasets:[{ data:vals, backgroundColor:colors, borderWidth:2, borderColor:'#fff' }] },
            options: {
                responsive:true,
                plugins: { legend:{ position:'bottom', labels:{ font:{size:11}, boxWidth:12 } } },
                cutout:'60%'
            }
        });

    } catch(e) {
        console.error('Activity load error:', e);
    }
}

function resetProgressBar() {
    const bar = document.getElementById('refresh-bar');
    bar.style.transition = 'none';
    bar.style.width = '100%';
    setTimeout(() => {
        if(intervalMs > 0) {
            bar.style.transition = `width linear ${intervalMs/1000}s`;
            bar.style.width = '0%';
        }
    }, 50);
}

function changerIntervalle() {
    intervalMs = parseInt(document.getElementById('refresh-interval').value);
    clearInterval(refreshTimer);
    clearInterval(countdownTimer);
    if(intervalMs > 0) {
        refreshTimer = setInterval(() => { chargerActivites(); resetProgressBar(); }, intervalMs);
        resetProgressBar();
        document.getElementById('next-refresh').textContent = META.msgNext.replace('__S__', Math.round(intervalMs/1000));
    } else {
        document.getElementById('refresh-bar').style.width = '100%';
        document.getElementById('next-refresh').textContent = META.msgManual;
    }
}

// Démarrage
chargerActivites();
refreshTimer = setInterval(() => { chargerActivites(); resetProgressBar(); }, intervalMs);
resetProgressBar();
</script>
@endpush
