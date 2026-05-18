<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<title>{{ __('signatures.pdf_title', ['ref' => $collecte->numero_bordereau]) }}</title>
<style>
    body{font-family:Arial,sans-serif;font-size:10pt;color:#1A2332;padding:15mm;}
    h1{color:#1B6B3A;border-bottom:3px solid #1B6B3A;padding-bottom:10px;font-size:16pt;margin-top:0;}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:10px 0;}
    .field{background:#f9fafb;border:1px solid #e5e9ef;border-radius:4px;padding:8px;}
    .field label{font-size:8pt;color:#6b7280;font-weight:bold;text-transform:uppercase;display:block;}
    table{width:100%;border-collapse:collapse;margin:10px 0;}
    th,td{border:1px solid #e5e9ef;padding:6px 8px;font-size:9pt;}
    th{background:#f0fdf4;font-weight:bold;color:#1B6B3A;}
    tfoot td{background:#f0fdf4;font-weight:bold;}
    .footer{margin-top:20px;text-align:center;font-size:8pt;color:#9ca3af;border-top:1px solid #e5e9ef;padding-top:10px;}
    .sig-block{margin-top:25px;border:2px solid #1B6B3A;border-radius:6px;padding:15px;background:#f0fdf4;}
    .sig-block .head{font-size:9pt;color:#1B6B3A;font-weight:bold;text-transform:uppercase;margin-bottom:8px;}
    .sig-image{background:#fff;border:1px solid #d8e6dc;border-radius:4px;padding:8px;text-align:center;}
    .sig-image img{max-height:120px;}
    .sig-meta{font-size:8.5pt;color:#475569;margin-top:8px;line-height:1.5;}
    .sig-meta strong{color:#1A2332;}
    .mention{background:#fff8e1;border-left:3px solid #f9d77e;padding:6px 10px;margin-top:8px;font-style:italic;font-size:9pt;}
    .integrity{font-family:'Courier New',monospace;font-size:7.5pt;color:#6b7280;}
</style>
</head>
<body>

<h1>📋 {{ __('signatures.pdf_h1') }}</h1>

<div class="grid">
    <div class="field"><label>{{ __('pdf.field_etab') }}</label>{{ $etablissement->nom ?? '—' }}</div>
    <div class="field"><label>{{ __('pdf.field_bordereau') }}</label><strong>{{ $collecte->numero_bordereau }}</strong></div>
    <div class="field"><label>{{ __('pdf.field_date') }}</label>{{ \Carbon\Carbon::parse($collecte->date_collecte)->format('d/m/Y H:i') }}</div>
    <div class="field"><label>{{ __('pdf.field_vehicle') }}</label>{{ $collecte->vehicule ?? 'N/A' }}</div>
    <div class="field"><label>{{ __('pdf.field_containers') }}</label><strong>{{ $collecte->nombre_contenants }}</strong></div>
    <div class="field"><label>{{ __('pdf.field_weight') }}</label><strong>{{ number_format($collecte->poids_declare_kg ?? 0, 2) }} kg</strong></div>
</div>

<h3 style="color:#1B6B3A;margin-top:20px;font-size:11pt;">{{ __('signatures.pdf_table_detail') }}</h3>
<table>
<thead>
    <tr>
        <th>{{ __('pdf.col_index') }}</th>
        <th>{{ __('pdf.col_service') }}</th>
        <th>{{ __('pdf.col_container_type') }}</th>
        <th>{{ __('pdf.col_qty') }}</th>
        <th>{{ __('pdf.col_weight_est') }}</th>
    </tr>
</thead>
<tbody>
@foreach($declarations as $i => $d)
    <tr>
        <td>{{ $i+1 }}</td>
        <td>{{ $d->service_nom }}</td>
        <td>{{ $d->contenant_nom }}</td>
        <td>{{ $d->nombre_contenants }}</td>
        <td>{{ number_format($d->poids_estime_kg, 2) }} kg</td>
    </tr>
@endforeach
</tbody>
<tfoot>
    <tr>
        <td colspan="3">{{ __('pdf.col_total') }}</td>
        <td>{{ $declarations->sum('nombre_contenants') }}</td>
        <td>{{ number_format($declarations->sum('poids_estime_kg'), 2) }} kg</td>
    </tr>
</tfoot>
</table>

{{-- ── Bloc signature électronique ────────────────────────── --}}
<div class="sig-block">
    <div class="head">{{ __('signatures.pdf_sig_block_head') }}</div>
    <div class="sig-image">
        <img src="{{ $signatureImg }}" alt="Signature" />
    </div>

    <div class="mention">« {{ $signature->commentaire }} »</div>

    <div class="sig-meta">
        <strong>{{ __('signatures.pdf_field_signer') }}</strong> {{ $signature->signataire_nom }}
        @if($signature->signataire_fonction) — {{ $signature->signataire_fonction }} @endif
        <br>
        <strong>{{ __('signatures.pdf_field_datetime') }}</strong>
        {{ $signature->signed_at?->format('d/m/Y H:i:s') }} (UTC{{ $signature->signed_at?->format('P') }})
        <br>
        <strong>{{ __('signatures.pdf_field_ip') }}</strong> {{ $signature->ip_address }}
        @php $info = $signature->device_info ?? []; @endphp
        @if(!empty($info['os']) || !empty($info['browser']))
            <br><strong>{{ __('signatures.pdf_field_device') }}</strong>
            {{ $info['os'] ?? '' }} {{ !empty($info['browser']) ? '— '.$info['browser'] : '' }}
        @endif
    </div>
</div>

<div class="footer">
    {{ __('signatures.pdf_footer_label', ['date' => now()->format('d/m/Y H:i')]) }}
    <br>
    <span class="integrity">
        {{ __('signatures.pdf_integrity_label') }} {{ $signature->signature_hash }}
    </span>
</div>

</body>
</html>
