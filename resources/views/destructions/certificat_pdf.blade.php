<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; color:#1a1a1a; }

.page { padding:20px 25px; }

/* En-tête société */
.entete-societe { margin-bottom:18px; }
.logo-area { display:inline-block; width:60px; height:60px; border:2px solid #1B6B3A; border-radius:50%; text-align:center; line-height:56px; font-size:22px; color:#1B6B3A; font-weight:bold; float:left; margin-right:12px; }
.societe-info { overflow:hidden; font-size:9px; line-height:1.5; }
.societe-info strong { font-size:11px; }

/* Titre */
.titre-bordereau { text-align:center; margin:14px 0 4px; font-size:18px; font-weight:bold; text-decoration:underline; }
.sous-titre { text-align:center; font-size:9px; color:#555; margin-bottom:12px; }

/* Numéro */
.num-bordereau { border:1px solid #1a1a1a; padding:5px 10px; font-size:11px; font-weight:bold; margin-bottom:10px; display:inline-block; }

/* Grilles */
table.grid { width:100%; border-collapse:collapse; margin-bottom:8px; }
table.grid td, table.grid th {
    border:1px solid #555;
    padding:5px 7px;
    vertical-align:top;
    font-size:9.5px;
    line-height:1.5;
}
table.grid th { font-weight:bold; font-size:9px; }
table.grid .cell-title { font-weight:bold; font-size:9px; background:#f5f5f5; }

/* Section header */
.section-header {
    background:#1B6B3A; color:#fff; text-align:center;
    font-weight:bold; font-size:10px; padding:5px;
    letter-spacing:.05em;
}

/* Signature */
.signature-box { border:1px solid #555; height:60px; width:180px; display:inline-block; margin-top:6px; }
.footer-note { font-size:8px; color:#777; text-align:center; margin-top:12px; border-top:1px solid #ccc; padding-top:6px; }

.clearfix::after { content:""; display:table; clear:both; }
</style>
</head>
<body>
@php
    $methodKey = match($destruction->methode){
        'incineration'=>'incineration',
        'autoclave'=>'autoclave',
        'desinfection_chimique'=>'desinfection',
        'autre'=>'other',
        default=>'other',
    };
@endphp
<div class="page">

    {{-- ══ EN-TÊTE SOCIÉTÉ ══ --}}
    <div class="entete-societe clearfix">
        <div class="logo-area">🧬</div>
        <div class="societe-info">
            <strong>{{ strtoupper($etablissement->nom ?? __('pdf.establishment_fallback')) }}</strong><br>
            {{ $etablissement->adresse ?? '' }}{{ $etablissement->ville ? ', '.$etablissement->ville : '' }}<br>
            @if($etablissement->telephone ?? null) {{ __('destructions.cert_field_phone') }} : {{ $etablissement->telephone }}<br>@endif
            @if($etablissement->email ?? null) {{ __('destructions.cert_field_email') }} : {{ $etablissement->email }}<br>@endif
        </div>
    </div>

    {{-- ══ TITRE ══ --}}
    <div class="titre-bordereau">{{ __('destructions.cert_title') }}</div>
    <div class="sous-titre">{{ __('destructions.cert_subtitle_emitter') }}</div>

    <div class="num-bordereau">{{ __('destructions.cert_num_label') }} {{ $certificatNum }}</div>

    {{-- ══ SECTION ÉMETTEUR ══ --}}
    <table class="grid">
        <tr>
            <td style="width:50%">
                <span class="cell-title">{{ __('destructions.cert_section_emitter') }}</span><br>
                {{ __('destructions.cert_section_emitter_sub') }}<br><br>
                {{ __('destructions.cert_field_name') }} : <strong>{{ $etablissement->nom ?? '—' }}</strong><br>
                {{ __('destructions.cert_field_address') }} : {{ $etablissement->adresse ?? '—' }}<br>
                {{ __('destructions.cert_field_city') }} : {{ $etablissement->ville ?? '—' }}<br>
                {{ __('destructions.cert_field_phone') }} : {{ $etablissement->telephone ?? '—' }}<br>
                {{ __('destructions.cert_field_email') }} : {{ $etablissement->email ?? '—' }}
            </td>
            <td style="width:50%">
                <span class="cell-title">{{ __('destructions.cert_section_dest') }}</span><br><br>
                {{ __('destructions.cert_field_name') }} : {{ $destruction->site_traitement ?? '—' }}<br>
                {{ __('destructions.cert_field_address') }} : —<br>
                {{ __('destructions.cert_field_contact') }} : {{ isset($prestataire) ? ($prestataire->prenom.' '.$prestataire->nom) : '—' }}
            </td>
        </tr>
    </table>

    {{-- ══ DÉNOMINATION ══ --}}
    <table class="grid">
        <tr>
            <td>
                <span class="cell-title">{{ __('destructions.cert_section_waste') }}</span><br>
                {{ __('destructions.cert_waste_rubric') }}<br>
                <strong>{{ __('destructions.cert_waste_form') }}</strong>
            </td>
        </tr>
    </table>

    {{-- ══ CONDITIONNEMENT ══ --}}
    <table class="grid">
        <tr>
            <td>
                <span class="cell-title">{{ __('destructions.cert_section_packaging') }}</span><br>
                {{ __('destructions.cert_packaging_type') }}
            </td>
        </tr>
    </table>

    {{-- ══ QUANTITÉ + DÉTAILS ══ --}}
    <table class="grid">
        <tr>
            <td style="width:50%">
                <span class="cell-title">{{ __('destructions.cert_section_qty') }}</span><br><br>
                {{ __('destructions.cert_qty_real') }}<br>
                <strong>{{ number_format($destruction->poids_reel_kg, 2) }} kg</strong><br><br>
                {{ __('destructions.cert_qty_declared') }} {{ number_format($collecte->poids_declare_kg ?? 0, 2) }} kg<br>
                {{ __('destructions.cert_qty_bordereau') }} <strong>{{ $collecte->numero_bordereau ?? '—' }}</strong>
            </td>
            <td style="width:50%">
                <span class="cell-title">{{ __('destructions.cert_section_method') }}</span><br><br>
                <strong>{{ __('destructions.method_' . $methodKey . '_full') }}</strong><br><br>
                {{ __('destructions.cert_method_conform') }}
                <strong>{{ $destruction->conforme ? __('destructions.conform_yes_full') : __('destructions.conform_no_full') }}</strong>
            </td>
        </tr>
    </table>

    {{-- ══ SECTION TRANSPORTEUR ══ --}}
    <table class="grid" style="margin-top:4px;">
        <tr>
            <td colspan="2" class="section-header">{{ __('destructions.cert_subtitle_carrier') }}</td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="cell-title">{{ __('destructions.cert_section_carrier') }}</span><br>
                {{ __('destructions.cert_field_name') }} : {{ $destruction->site_traitement ?? '—' }}<br>
                {{ __('destructions.cert_field_address') }} : —<br>
                {{ __('destructions.cert_field_phone') }} : —<br>
                {{ __('destructions.cert_carrier_mode') }}
            </td>
        </tr>
    </table>

    {{-- ══ DÉCLARATION ÉMETTEUR ══ --}}
    <table class="grid" style="margin-top:4px;">
        <tr>
            <td colspan="2" class="section-header">{{ __('destructions.cert_subtitle_decl') }}</td>
        </tr>
        <tr>
            <td style="width:60%">
                <span class="cell-title">{{ __('destructions.cert_section_decl') }}</span><br>
                {{ __('destructions.cert_decl_text') }}<br><br>
                {{ __('destructions.cert_field_signname') }} : <strong>{{ $etablissement->responsable_qhse ?? $etablissement->nom ?? '—' }}</strong>
                &nbsp;&nbsp;&nbsp; {{ __('destructions.cert_field_signdate') }} : <strong>{{ \Carbon\Carbon::parse($destruction->date_destruction)->format('d/m/Y') }}</strong>
            </td>
            <td style="width:40%; text-align:center; vertical-align:middle;">
                <div style="font-size:9px; margin-bottom:4px;">{{ __('destructions.cert_field_stamp') }}</div>
                <div class="signature-box"></div>
            </td>
        </tr>
    </table>

    {{-- ══ SECTION INSTALLATION DE DESTINATION ══ --}}
    <table class="grid" style="margin-top:4px;">
        <tr>
            <td colspan="2" class="section-header">{{ __('destructions.cert_subtitle_dest') }}</td>
        </tr>
        <tr>
            <td style="width:60%">
                <span class="cell-title">{{ __('destructions.cert_section_reception') }}</span><br>
                {{ __('destructions.cert_field_name') }} : <strong>{{ strtoupper($destruction->site_traitement ?? '—') }}</strong><br>
                {{ __('destructions.cert_field_address') }} : —<br><br>
                {{ __('destructions.cert_qty_received') }} <strong>{{ number_format($destruction->poids_reel_kg, 2) }} kg</strong><br><br>
                @if($destruction->date_reception)
                {{ __('destructions.cert_date_reception') }} <strong>{{ \Carbon\Carbon::parse($destruction->date_reception)->format('d/m/Y') }}</strong>
                @endif
            </td>
            <td style="width:40%">
                {{ __('destructions.show_cert_num') }} : <strong>{{ $certificatNum }}</strong><br><br>
                {{ __('destructions.cert_date_destruction') }} <strong>{{ \Carbon\Carbon::parse($destruction->date_destruction)->format('d/m/Y') }}</strong><br><br>
                @if($destruction->notes)
                {{ __('destructions.show_notes') }} : {{ $destruction->notes }}
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="cell-title">{{ __('destructions.cert_section_realisation') }}</span><br>
                {{ __('destructions.cert_real_desc') }} {{ __('destructions.method_' . $methodKey . '_full') }} —
                {{ __('destructions.cert_real_compliance') }}
                @if($destruction->notes)<br>{{ __('destructions.cert_observations') }} {{ $destruction->notes }}@endif
            </td>
        </tr>
        <tr>
            <td style="width:60%; vertical-align:top; padding-top:6px;">
                &nbsp;
            </td>
            <td style="width:40%; text-align:center;">
                <div style="font-size:9px; margin-bottom:4px;">{{ __('destructions.cert_field_stamp') }}</div>
                <div class="signature-box"></div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        {{ __('pdf.doc_generated_on', ['date' => now()->format('d/m/Y H:i')]) }} —
        {{ __('pdf.platform_dechets') }} —
        {{ __('pdf.destruction_compliance_notice') }}
    </div>

</div>
</body>
</html>
