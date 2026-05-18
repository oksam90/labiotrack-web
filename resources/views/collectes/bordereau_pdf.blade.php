<!DOCTYPE html><html lang="{{ app()->getLocale() }}"><head><meta charset="UTF-8">
<style>
body{font-family:Arial,sans-serif;font-size:10pt;color:#1A2332;padding:15mm;}
h1{color:#1B6B3A;border-bottom:3px solid #1B6B3A;padding-bottom:10px;font-size:16pt;}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:10px 0;}
.field{background:#f9fafb;border:1px solid #e5e9ef;border-radius:4px;padding:8px;}
.field label{font-size:8pt;color:#6b7280;font-weight:bold;text-transform:uppercase;display:block;}
table{width:100%;border-collapse:collapse;margin:10px 0;}
th,td{border:1px solid #e5e9ef;padding:6px 8px;font-size:9pt;}
th{background:#f0fdf4;font-weight:bold;color:#1B6B3A;}
tfoot td{background:#f0fdf4;font-weight:bold;}
.footer{margin-top:20px;text-align:center;font-size:8pt;color:#9ca3af;border-top:1px solid #e5e9ef;padding-top:10px;}
.sig{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;}
.sig-box{border:1px solid #e5e9ef;border-radius:4px;padding:15px;text-align:center;}
</style></head><body>
<h1>📋 {{ __('collectes.header_show') }}</h1>
<div class="grid">
    <div class="field"><label>{{ __('pdf.field_etab') }}</label>{{ $etablissement->nom }}</div>
    <div class="field"><label>{{ __('pdf.field_bordereau') }}</label><strong>{{ $collecte->numero_bordereau }}</strong></div>
    <div class="field"><label>{{ __('pdf.field_date') }}</label>{{ \Carbon\Carbon::parse($collecte->date_collecte)->format('d/m/Y H:i') }}</div>
    <div class="field"><label>{{ __('pdf.field_vehicle') }}</label>{{ $collecte->vehicule ?? 'N/A' }}</div>
    <div class="field"><label>{{ __('pdf.field_containers') }}</label><strong>{{ $collecte->nombre_contenants }}</strong></div>
    <div class="field"><label>{{ __('pdf.field_weight') }}</label><strong>{{ number_format($collecte->poids_declare_kg,2) }} kg</strong></div>
</div>
<h3 style="color:#1B6B3A;margin-top:20px;">{{ __('pdf.table_detail_title') }}</h3>
<table>
<thead><tr>
    <th>{{ __('pdf.col_index') }}</th>
    <th>{{ __('pdf.col_service') }}</th>
    <th>{{ __('pdf.col_container_type') }}</th>
    <th>{{ __('pdf.col_qty') }}</th>
    <th>{{ __('pdf.col_weight_est') }}</th>
</tr></thead>
<tbody>
@foreach($declarations as $i => $d)
<tr><td>{{ $i+1 }}</td><td>{{ $d->service_nom }}</td><td>{{ $d->contenant_nom }}</td><td>{{ $d->nombre_contenants }}</td><td>{{ number_format($d->poids_estime_kg,2) }} kg</td></tr>
@endforeach
</tbody>
<tfoot><tr><td colspan="3">{{ __('pdf.col_total') }}</td><td>{{ $declarations->sum('nombre_contenants') }}</td><td>{{ number_format($declarations->sum('poids_estime_kg'),2) }} kg</td></tr></tfoot>
</table>
<div class="sig">
<div class="sig-box"><div style="font-size:8pt;color:#6b7280;text-transform:uppercase;margin-bottom:40px;">{{ __('pdf.sig_etab_responsible') }}</div><div style="border-top:1px solid #333;padding-top:5px;font-size:8pt;">{{ __('pdf.sig_name_stamp') }}</div></div>
<div class="sig-box"><div style="font-size:8pt;color:#6b7280;text-transform:uppercase;margin-bottom:40px;">{{ __('pdf.sig_collecteur') }}</div><div style="border-top:1px solid #333;padding-top:5px;font-size:8pt;">{{ __('pdf.sig_name_stamp') }}</div></div>
</div>
<div class="footer">{{ __('pdf.generated_on', ['date' => now()->format('d/m/Y H:i')]) }} — {{ __('pdf.platform_legacy') }}</div>
</body></html>
