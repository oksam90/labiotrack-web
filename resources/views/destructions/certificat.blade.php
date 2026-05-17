@extends('layouts.app')
@section('title', __('destructions.page_certificat_title') . ' '.$destruction->certificat_numero)
@section('content')

@php
    $methodKey = match($destruction->methode){'incineration'=>'incineration','autoclave'=>'autoclave','desinfection_chimique'=>'desinfection','autre'=>'other',default=>'other'};
@endphp

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2 text-success"></i>{{ __('destructions.header_certificat') }}</h4>
        <small class="text-muted">{{ __('destructions.cert_num_label') }} {{ $destruction->certificat_numero }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('destructions.certificat.pdf', $destruction->id) }}" class="btn btn-danger btn-sm">
            <i class="bi bi-file-pdf me-1"></i>{{ __('destructions.btn_download_pdf') }}
        </a>
        <a href="{{ route('destructions.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('destructions.btn_back') }}</a>
    </div>
</div>

<div class="card mx-auto" style="max-width:820px; border:1px solid #dee2e6;">
  <div class="card-body p-4">

    {{-- EN-TÊTE SOCIÉTÉ --}}
    <div class="d-flex align-items-start gap-3 mb-4 pb-3 border-bottom">
        <div style="width:64px;height:64px;border:2px solid #1B6B3A;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;">🧬</div>
        <div>
            <div class="fw-bold" style="font-size:1rem;">{{ strtoupper($etablissement->nom ?? '—') }}</div>
            <div style="font-size:.82rem;color:#555;line-height:1.6;">
                {{ $etablissement->adresse ?? '' }}{{ $etablissement->ville ? ', '.$etablissement->ville : '' }}<br>
                @if($etablissement->telephone ?? null) {{ __('destructions.cert_field_phone') }} : {{ $etablissement->telephone }}<br>@endif
                @if($etablissement->email ?? null) {{ __('destructions.cert_field_email') }} : {{ $etablissement->email }}@endif
            </div>
        </div>
    </div>

    {{-- TITRE --}}
    <h3 class="text-center fw-bold text-decoration-underline mb-1" style="font-size:1.3rem;">
        {{ __('destructions.cert_title') }}
    </h3>
    <p class="text-center text-muted mb-3" style="font-size:.82rem;">{{ __('destructions.cert_subtitle_emitter') }}</p>
    <div class="d-inline-block border px-3 py-1 mb-3 fw-bold" style="font-size:.95rem;">
        {{ __('destructions.cert_num_label') }} {{ $destruction->certificat_numero }}
    </div>

    {{-- EMETTEUR + DESTINATION --}}
    <table class="table table-bordered mb-3" style="font-size:.85rem;">
      <tbody>
        <tr>
          <td style="width:50%;vertical-align:top;">
            <strong>{{ __('destructions.cert_section_emitter') }}</strong><br>
            {{ __('destructions.cert_section_emitter_sub') }}<br><br>
            {{ __('destructions.cert_field_name') }} : <strong>{{ $etablissement->nom ?? '—' }}</strong><br>
            {{ __('destructions.cert_field_address') }} : {{ $etablissement->adresse ?? '—' }}<br>
            {{ __('destructions.cert_field_city') }} : {{ $etablissement->ville ?? '—' }}<br>
            {{ __('destructions.cert_field_phone') }} : {{ $etablissement->telephone ?? '—' }}<br>
            {{ __('destructions.cert_field_email') }} : {{ $etablissement->email ?? '—' }}
          </td>
          <td style="width:50%;vertical-align:top;">
            <strong>{{ __('destructions.cert_section_dest') }}</strong><br><br>
            {{ __('destructions.cert_field_name') }} : {{ $destruction->site_traitement ?? '—' }}<br>
            {{ __('destructions.cert_field_contact') }} : {{ isset($prestataire) ? ($prestataire->prenom.' '.$prestataire->nom) : '—' }}
          </td>
        </tr>
      </tbody>
    </table>

    {{-- DÉCHET + CONDITIONNEMENT --}}
    <table class="table table-bordered mb-3" style="font-size:.85rem;">
      <tbody>
        <tr>
          <td>
            <strong>{{ __('destructions.cert_section_waste') }}</strong><br>
            {{ __('destructions.cert_waste_rubric') }}<br>
            <strong>{{ __('destructions.cert_waste_form') }}</strong>
          </td>
        </tr>
        <tr>
          <td>
            <strong>{{ __('destructions.cert_section_packaging') }}</strong><br>
            {{ __('destructions.cert_packaging_type') }}
          </td>
        </tr>
      </tbody>
    </table>

    {{-- QUANTITÉ + MÉTHODE --}}
    <table class="table table-bordered mb-3" style="font-size:.85rem;">
      <tbody>
        <tr>
          <td style="width:50%;vertical-align:top;">
            <strong>{{ __('destructions.cert_section_qty') }}</strong><br><br>
            {{ __('destructions.cert_qty_real') }} <strong>{{ number_format($destruction->poids_reel_kg, 2) }} kg</strong><br>
            {{ __('destructions.cert_qty_declared') }} {{ number_format($collecte->poids_declare_kg ?? 0, 2) }} kg<br>
            {{ __('destructions.cert_qty_bordereau') }} <strong>{{ $collecte->numero_bordereau ?? '—' }}</strong>
          </td>
          <td style="width:50%;vertical-align:top;">
            <strong>{{ __('destructions.cert_section_method') }}</strong><br><br>
            <strong>{{ __('destructions.method_' . $methodKey . '_full') }}</strong><br><br>
            {{ __('destructions.cert_method_conform') }}
            <span class="badge bg-{{ $destruction->conforme ? 'success' : 'danger' }}">
                {{ $destruction->conforme ? __('destructions.conform_yes_full') : __('destructions.conform_no_full') }}
            </span>
          </td>
        </tr>
      </tbody>
    </table>

    {{-- SECTION TRANSPORTEUR --}}
    <div class="text-center fw-bold py-2 mb-0" style="background:#1B6B3A;color:#fff;font-size:.82rem;letter-spacing:.05em;">
        {{ __('destructions.cert_subtitle_carrier') }}
    </div>
    <table class="table table-bordered mb-3" style="font-size:.85rem;">
      <tbody>
        <tr>
          <td>
            <strong>{{ __('destructions.cert_section_carrier') }}</strong><br>
            {{ __('destructions.cert_field_name') }} : {{ $destruction->site_traitement ?? '—' }}<br>
            {{ __('destructions.cert_carrier_mode') }}
          </td>
        </tr>
      </tbody>
    </table>

    {{-- DÉCLARATION EMETTEUR --}}
    <div class="text-center fw-bold py-2 mb-0" style="background:#1B6B3A;color:#fff;font-size:.82rem;letter-spacing:.05em;">
        {{ __('destructions.cert_subtitle_decl') }}
    </div>
    <table class="table table-bordered mb-3" style="font-size:.85rem;">
      <tbody>
        <tr>
          <td style="width:65%;vertical-align:top;">
            <strong>{{ __('destructions.cert_section_decl') }}</strong><br>
            {{ __('destructions.cert_decl_text') }}<br><br>
            {{ __('destructions.cert_field_signname') }} : <strong>{{ $etablissement->responsable_qhse ?? $etablissement->nom ?? '—' }}</strong>
            &nbsp;&nbsp;&nbsp;
            {{ __('destructions.cert_field_signdate') }} : <strong>{{ \Carbon\Carbon::parse($destruction->date_destruction)->format('d/m/Y') }}</strong>
          </td>
          <td style="width:35%;text-align:center;vertical-align:middle;">
            <div style="font-size:.78rem;color:#555;margin-bottom:4px;">{{ __('destructions.cert_field_stamp') }}</div>
            <div style="height:70px;border:1px solid #999;border-radius:4px;"></div>
          </td>
        </tr>
      </tbody>
    </table>

    {{-- SECTION INSTALLATION DE DESTINATION --}}
    <div class="text-center fw-bold py-2 mb-0" style="background:#1B6B3A;color:#fff;font-size:.82rem;letter-spacing:.05em;">
        {{ __('destructions.cert_subtitle_dest') }}
    </div>
    <table class="table table-bordered mb-3" style="font-size:.85rem;">
      <tbody>
        <tr>
          <td style="width:60%;vertical-align:top;">
            <strong>{{ __('destructions.cert_section_reception') }}</strong><br>
            {{ __('destructions.cert_field_name') }} : <strong>{{ strtoupper($destruction->site_traitement ?? '—') }}</strong><br><br>
            {{ __('destructions.cert_qty_received') }} <strong>{{ number_format($destruction->poids_reel_kg, 2) }} kg</strong><br><br>
            @if($destruction->date_reception)
            {{ __('destructions.cert_date_reception') }} <strong>{{ \Carbon\Carbon::parse($destruction->date_reception)->format('d/m/Y') }}</strong>
            @endif
          </td>
          <td style="width:40%;vertical-align:top;">
            {{ __('destructions.show_cert_num') }} : <strong>{{ $destruction->certificat_numero }}</strong><br><br>
            {{ __('destructions.cert_date_destruction') }} <strong>{{ \Carbon\Carbon::parse($destruction->date_destruction)->format('d/m/Y') }}</strong>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <strong>{{ __('destructions.cert_section_realisation') }}</strong><br>
            {{ __('destructions.cert_real_desc') }} {{ __('destructions.method_' . $methodKey . '_full') }} —
            {{ __('destructions.cert_real_compliance') }}
            @if($destruction->notes)<br>{{ __('destructions.cert_observations') }} {{ $destruction->notes }}@endif
          </td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td style="text-align:center;">
            <div style="font-size:.78rem;color:#555;margin-bottom:4px;">{{ __('destructions.cert_field_stamp') }}</div>
            <div style="height:70px;border:1px solid #999;border-radius:4px;"></div>
          </td>
        </tr>
      </tbody>
    </table>

    <p class="text-center text-muted mt-3" style="font-size:.72rem;">
        {{ __('destructions.cert_generated_on', ['date' => now()->format('d/m/Y H:i')]) }}
    </p>
  </div>
</div>
@endsection
