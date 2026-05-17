@extends('layouts.app')
@section('title', __('checklists.page_index_title'))
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-check2-square me-2 text-success"></i>{{ __('checklists.header_index') }}</h4>
        <small class="text-muted">{{ __('checklists.subtitle_index') }}</small>
    </div>
    <a href="{{ route('checklists.create') }}" class="btn btn-primary"><i class="bi bi-plus me-1"></i>{{ __('checklists.btn_new') }}</a>
</div>

<!-- Score moyen -->
@if($scoresMoyen)
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="mb-2" style="font-size:.85rem;color:#6b7280;font-weight:600;">{{ __('checklists.kpi_score') }}</div>
            <div style="font-size:3rem;font-weight:800;color:{{ $scoresMoyen >= 80 ? '#1B6B3A' : ($scoresMoyen >= 60 ? '#D4A017' : '#C0392B') }};">
                {{ number_format($scoresMoyen, 0) }}%
            </div>
            <div class="progress mt-2" style="height:8px;">
                <div class="progress-bar {{ $scoresMoyen >= 80 ? 'bg-success' : ($scoresMoyen >= 60 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $scoresMoyen }}%"></div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('checklists.col_date') }}</th>
                    <th>{{ __('checklists.col_service') }}</th>
                    <th>{{ __('checklists.col_agent') }}</th>
                    <th>{{ __('checklists.col_boxes') }}</th>
                    <th>{{ __('checklists.col_labels') }}</th>
                    <th>{{ __('checklists.col_ventilated') }}</th>
                    <th>{{ __('checklists.col_epi') }}</th>
                    <th>{{ __('checklists.col_score') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($checklists as $cl)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($cl->date_checklist)->format('d/m/Y') }}</td>
                    <td>{{ $cl->service_nom ?? '—' }}</td>
                    <td><small class="text-muted">{{ $cl->agent_nom }}</small></td>
                    <td class="text-center">@if($cl->boites_fermees_75)<i class="bi bi-check-circle-fill text-success"></i>@else<i class="bi bi-x-circle-fill text-danger"></i>@endif</td>
                    <td class="text-center">@if($cl->sacs_correctement_etiquetes)<i class="bi bi-check-circle-fill text-success"></i>@else<i class="bi bi-x-circle-fill text-danger"></i>@endif</td>
                    <td class="text-center">@if($cl->local_ventile)<i class="bi bi-check-circle-fill text-success"></i>@else<i class="bi bi-x-circle-fill text-danger"></i>@endif</td>
                    <td class="text-center">@if($cl->epi_port)<i class="bi bi-check-circle-fill text-success"></i>@else<i class="bi bi-x-circle-fill text-danger"></i>@endif</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-fill" style="height:6px;min-width:60px;">
                                <div class="progress-bar {{ $cl->score_conformite >= 80 ? 'bg-success' : ($cl->score_conformite >= 60 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $cl->score_conformite }}%"></div>
                            </div>
                            <span class="fw-bold" style="font-size:.88rem;">{{ number_format($cl->score_conformite,0) }}%</span>
                        </div>
                    </td>
                    <td><a href="{{ route('checklists.show', $cl->id) }}" class="btn btn-sm btn-outline-primary py-0"><i class="bi bi-eye"></i></a></td>
                </tr>
                @endforeach
                @if($checklists->isEmpty())
                <tr><td colspan="9" class="text-center text-muted py-4">{{ __('checklists.empty_list') }}</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $checklists->links() }}</div>
</div>
@endsection
