<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1a1a1a; padding:20px; }
        .header { background:#1B6B3A; color:#fff; padding:20px; margin-bottom:20px; }
        .header h1 { font-size:18px; margin-bottom:4px; }
        .header p { font-size:11px; opacity:.85; }
        .section { margin-bottom:20px; }
        .section-title { font-size:13px; font-weight:bold; color:#1B6B3A; border-bottom:2px solid #1B6B3A; padding-bottom:4px; margin-bottom:10px; }
        .kpi-grid { display:table; width:100%; margin-bottom:15px; }
        .kpi-box { display:table-cell; border:1px solid #dee2e6; padding:12px; text-align:center; width:25%; }
        .kpi-val { font-size:20px; font-weight:bold; color:#1B6B3A; }
        .kpi-lbl { font-size:9px; color:#666; margin-top:3px; }
        table.data { width:100%; border-collapse:collapse; margin-bottom:10px; }
        table.data th { background:#f0f7f0; color:#1B6B3A; padding:6px 8px; border:1px solid #dee2e6; font-size:10px; }
        table.data td { padding:5px 8px; border:1px solid #dee2e6; }
        table.data tr:nth-child(even) td { background:#fafafa; }
        .footer { margin-top:30px; border-top:1px solid #dee2e6; padding-top:10px; font-size:9px; color:#888; text-align:center; }
    </style>
</head>
<body>

<div class="header">
    <h1>🧬 {{ __('pdf.report_title') }}</h1>
    <p>
        {{ $etablissement->nom ?? __('pdf.unknown_structure') }} — {{ $etablissement->ville ?? '' }}
        &nbsp;|&nbsp;
        {{ __('pdf.period') }} : {{ \Carbon\Carbon::parse($request->periode_debut)->format('d/m/Y') }}
        {{ __('pdf.from_to') }} {{ \Carbon\Carbon::parse($request->periode_fin)->format('d/m/Y') }}
        &nbsp;|&nbsp;
        {{ __('pdf.report_generated_on', ['date' => now()->format('d/m/Y H:i')]) }}
    </p>
</div>

{{-- KPIs --}}
<div class="section">
    <div class="section-title">{{ __('pdf.report_kpi_section') }}</div>
    <div class="kpi-grid">
        <div class="kpi-box">
            <div class="kpi-val">{{ $data['total_declarations'] }}</div>
            <div class="kpi-lbl">{{ __('pdf.kpi_declarations') }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-val">{{ number_format($data['poids_total_estime'], 1) }} kg</div>
            <div class="kpi-lbl">{{ __('pdf.kpi_weight_total') }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-val">{{ $data['collectes_count'] }}</div>
            <div class="kpi-lbl">{{ __('pdf.kpi_collectes') }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-val">{{ number_format($data['poids_reel_total'], 1) }} kg</div>
            <div class="kpi-lbl">{{ __('pdf.kpi_weight_real') }}</div>
        </div>
    </div>
    <div class="kpi-grid">
        <div class="kpi-box">
            <div class="kpi-val">{{ $data['destructions_count'] }}</div>
            <div class="kpi-lbl">{{ __('pdf.kpi_destructions') }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-val">{{ number_format($data['score_conformite'] ?? 0, 1) }}%</div>
            <div class="kpi-lbl">{{ __('pdf.kpi_score_avg') }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-val">{{ $data['alertes_count'] }}</div>
            <div class="kpi-lbl">{{ __('pdf.kpi_alerts') }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-val">{{ $etablissement->nombre_lits ?? 0 }}</div>
            <div class="kpi-lbl">{{ __('pdf.kpi_beds_declared') }}</div>
        </div>
    </div>
</div>

{{-- Par service --}}
@if(count($data['par_service']) > 0)
<div class="section">
    <div class="section-title">{{ __('pdf.report_service_section') }}</div>
    <table class="data">
        <thead>
            <tr>
                <th>{{ __('pdf.col_service') }}</th>
                <th>{{ __('pdf.col_decl_count') }}</th>
                <th>{{ __('pdf.col_weight_estimated_kg') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['par_service'] as $s)
            <tr>
                <td>{{ $s->nom }}</td>
                <td style="text-align:center">{{ $s->nb }}</td>
                <td style="text-align:right">{{ number_format($s->poids, 1) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="footer">
    {{ __('pdf.platform_name') }} — {{ $etablissement->nom ?? 'N/A' }} — {{ __('pdf.auto_generated_notice') }}
    @if($etablissement->responsable_qhse)
        | {{ __('pdf.qhse_responsible') }} : {{ $etablissement->responsable_qhse }}
    @endif
</div>

</body>
</html>
