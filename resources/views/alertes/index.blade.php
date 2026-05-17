@extends('layouts.app')
@section('title', __('alertes.page_title'))
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-bell me-2 text-danger"></i>{{ __('alertes.header') }}</h4>
    <form method="POST" action="{{ route('alertes.tout-lire') }}">@csrf
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-check-all me-1"></i>{{ __('alertes.btn_mark_all_read') }}</button>
    </form>
</div>
<div class="card">
    @foreach($alertes as $a)
    <div class="d-flex gap-3 p-3 border-bottom align-items-start {{ $a->lu ? 'opacity-60' : 'bg-light' }}">
        <div class="flex-shrink-0 mt-1">
            @if($a->niveau==='danger')<i class="bi bi-exclamation-circle-fill text-danger fs-5"></i>
            @elseif($a->niveau==='warning')<i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
            @else<i class="bi bi-info-circle-fill text-info fs-5"></i>@endif
        </div>
        <div class="flex-fill">
            <div class="fw-{{ $a->lu ? '400' : '600' }}">{{ $a->message }}</div>
            <div class="d-flex gap-3 mt-1">
                <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</small>
                @if($a->service_nom)<small class="text-muted"><i class="bi bi-hospital me-1"></i>{{ $a->service_nom }}</small>@endif
                <span class="badge bg-{{ $a->niveau==='danger'?'danger':($a->niveau==='warning'?'warning':'info') }} bg-opacity-10 text-{{ $a->niveau==='danger'?'danger':($a->niveau==='warning'?'warning':'info') }}" style="font-size:.7rem;">{{ ucfirst($a->type) }}</span>
            </div>
        </div>
        @if(!$a->lu)
        <form method="POST" action="{{ route('alertes.lire', $a->id) }}" class="flex-shrink-0">@csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2" title="{{ __('alertes.btn_mark_read') }}"><i class="bi bi-check"></i></button>
        </form>
        @endif
    </div>
    @endforeach
    @if($alertes->isEmpty())
    <div class="text-center py-5 text-muted"><i class="bi bi-check-circle-fill text-success fs-2"></i><p class="mt-2">{{ __('alertes.empty') }}</p></div>
    @endif
    <div class="card-footer">{{ $alertes->links() }}</div>
</div>
@endsection
