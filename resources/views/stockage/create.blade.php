@extends('layouts.app')
@section('title', __('stockage.page_create_title'))

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-box-arrow-in-down me-2 text-success"></i>{{ __('stockage.header_create') }}</h4>
        <small class="text-muted">{{ __('stockage.subtitle_create') }}</small>
    </div>
    <a href="{{ route('stockage.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>{{ __('stockage.btn_back') }}
    </a>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('stockage.store') }}" id="form-stockage"
                      data-msg-confirm="{{ __('common.confirm_action') }}">
                    @csrf

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('stockage.form_service_emitter') }} <span class="text-danger">*</span></label>
                            <select name="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                                <option value="">{{ __('stockage.form_select_placeholder') }}</option>
                                @foreach($services as $s)
                                    <option value="{{ $s->id }}" {{ old('service_id') == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>
                                @endforeach
                            </select>
                            @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('stockage.form_zone') }}</label>
                            <input type="text" name="zone_stockage" class="form-control"
                                   placeholder="{{ __('stockage.form_zone_ph') }}" value="{{ old('zone_stockage') }}" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('stockage.form_deadline') }}</label>
                            <input type="date" name="date_limite_collecte" class="form-control"
                                   value="{{ old('date_limite_collecte') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            <div class="form-text">{{ __('stockage.form_deadline_hint') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('stockage.form_notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="{{ __('stockage.form_notes_ph') }}" maxlength="500">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- SÉLECTION DES DÉCLARATIONS --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0">
                                {{ __('stockage.form_decl_label') }} <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-sm btn-light border" onclick="selectAll()">
                                <i class="bi bi-check-all me-1"></i>{{ __('stockage.btn_select_all') }}
                            </button>
                        </div>

                        @if($declarations->isEmpty())
                        <div class="p-4 text-center border rounded-3 text-muted">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            {{ __('stockage.form_no_decl') }}
                        </div>
                        @else
                        <div style="border:1px solid #dee2e6;border-radius:10px;max-height:300px;overflow-y:auto">
                            @foreach($declarations as $dec)
                            <label class="d-flex align-items-center gap-3 px-3 py-2 dec-row"
                                   style="border-bottom:1px solid #f0f0f0;cursor:pointer;transition:background .1s">
                                <input type="checkbox" name="declaration_ids[]" value="{{ $dec->id }}"
                                       class="form-check-input mt-0 dec-cb">
                                <div class="flex-grow-1" style="font-size:.85rem">
                                    <span class="fw-semibold">#{{ $dec->id }}</span>
                                    <span class="text-muted"> — {{ $dec->service_nom }}</span>
                                    <span class="badge bg-light text-dark border ms-1">{{ $dec->contenant_nom }}</span>
                                </div>
                                <div class="text-end" style="font-size:.8rem;white-space:nowrap">
                                    <strong>{{ $dec->nombre_contenants }}</strong> {{ __('stockage.form_count_short') }}
                                    <span class="text-muted ms-2">{{ number_format($dec->poids_estime_kg,1) }} kg</span>
                                </div>
                                <div class="text-muted" style="font-size:.75rem;white-space:nowrap">
                                    {{ \Carbon\Carbon::parse($dec->date_declaration)->format('d/m') }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @endif
                        @error('declaration_ids')
                        <div class="text-danger mt-1" style="font-size:.82rem">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- RÉCAPITULATIF AUTO --}}
                    <div id="recap" class="p-3 mb-4 rounded-3 d-none" style="background:#e8f5ee;border:1px solid #b0d4b8">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="fw-bold" style="font-size:1.3rem;color:var(--primary)" id="rc-lots">0</div>
                                <div style="font-size:.75rem;color:#6c757d">{{ __('stockage.recap_lots') }}</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold" style="font-size:1.3rem;color:var(--primary)" id="rc-cont">0</div>
                                <div style="font-size:.75rem;color:#6c757d">{{ __('stockage.recap_containers') }}</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold" style="font-size:1.3rem;color:#D4A017" id="rc-poids">0 kg</div>
                                <div style="font-size:.75rem;color:#6c757d">{{ __('stockage.recap_weight') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn px-4" style="background:var(--primary);color:#fff;font-weight:600"
                                @if($declarations->isEmpty()) disabled @endif>
                            <i class="bi bi-box-arrow-in-down me-2"></i>{{ __('stockage.btn_save_transfer') }}
                        </button>
                        <a href="{{ route('stockage.index') }}" class="btn btn-light px-3">{{ __('common.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="fw-semibold mb-2" style="font-size:.9rem"><i class="bi bi-info-circle me-2 text-primary"></i>{{ __('stockage.reminder_title') }}</div>
                <div style="font-size:.82rem;color:#555;line-height:1.7">
                    <div>{{ __('stockage.reminder_step1') }}</div>
                    <div>{{ __('stockage.reminder_step2') }}</div>
                    <div>{{ __('stockage.reminder_step3') }}</div>
                    <div class="mt-2 p-2 rounded" style="background:#fff3cd;font-size:.78rem">
                        {{ __('stockage.reminder_warning') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const decData = @json($declarations->keyBy('id')->map(fn($d) => ['qte' => $d->nombre_contenants, 'poids' => $d->poids_estime_kg]));

document.querySelectorAll('.dec-cb').forEach(cb => cb.addEventListener('change', updateRecap));
document.querySelectorAll('.dec-row').forEach(row => {
    row.addEventListener('mouseenter', () => row.style.background = '#f5faf6');
    row.addEventListener('mouseleave', () => row.style.background = '');
});

function selectAll() {
    document.querySelectorAll('.dec-cb').forEach(cb => cb.checked = true);
    updateRecap();
}

function updateRecap() {
    const checked = [...document.querySelectorAll('.dec-cb:checked')];
    if (!checked.length) { document.getElementById('recap').classList.add('d-none'); return; }
    document.getElementById('recap').classList.remove('d-none');
    document.getElementById('rc-lots').textContent  = checked.length;
    document.getElementById('rc-cont').textContent  = checked.reduce((s,cb) => s + (decData[cb.value]?.qte  || 0), 0);
    document.getElementById('rc-poids').textContent = checked.reduce((s,cb) => s + (decData[cb.value]?.poids || 0), 0).toFixed(1) + ' kg';
}
</script>
@endpush
