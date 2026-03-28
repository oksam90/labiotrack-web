<!DOCTYPE html>
<html lang="fr">
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
<div class="page">

    {{-- ══ EN-TÊTE SOCIÉTÉ ══ --}}
    <div class="entete-societe clearfix">
        <div class="logo-area">🧬</div>
        <div class="societe-info">
            <strong>{{ strtoupper($etablissement->nom ?? 'ÉTABLISSEMENT') }}</strong><br>
            {{ $etablissement->adresse ?? '' }}{{ $etablissement->ville ? ', '.$etablissement->ville : '' }}<br>
            @if($etablissement->telephone ?? null) Tél : {{ $etablissement->telephone }}<br>@endif
            @if($etablissement->email ?? null) Email : {{ $etablissement->email }}<br>@endif
        </div>
    </div>

    {{-- ══ TITRE ══ --}}
    <div class="titre-bordereau">Bordereau de destruction des déchets</div>
    <div class="sous-titre">— À REMPLIR PAR L'ÉMETTEUR DU BORDEREAU —</div>

    <div class="num-bordereau">Bordereau n° {{ $certificatNum }}</div>

    {{-- ══ SECTION ÉMETTEUR ══ --}}
    <table class="grid">
        <tr>
            <td style="width:50%">
                <span class="cell-title">1. Émetteur du bordereau :</span><br>
                Opérateur de gestion des déchets biomédicaux<br><br>
                Nom : <strong>{{ $etablissement->nom ?? '—' }}</strong><br>
                Adresse : {{ $etablissement->adresse ?? '—' }}<br>
                Ville : {{ $etablissement->ville ?? '—' }}<br>
                Tél : {{ $etablissement->telephone ?? '—' }}<br>
                Email : {{ $etablissement->email ?? '—' }}
            </td>
            <td style="width:50%">
                <span class="cell-title">2. Installation de destination :</span><br><br>
                Nom : {{ $destruction->site_traitement ?? 'Unité de Traitement' }}<br>
                Adresse : —<br>
                Personne à contacter : {{ isset($prestataire) ? ($prestataire->prenom.' '.$prestataire->nom) : '—' }}
            </td>
        </tr>
    </table>

    {{-- ══ DÉNOMINATION ══ --}}
    <table class="grid">
        <tr>
            <td>
                <span class="cell-title">3. Dénomination du déchet :</span><br>
                Rubrique déchet : Déchets d'Activités de Soins à Risques Infectieux (DASRI)<br>
                <strong>Déchets solides</strong> / liquides / gazeux (rayez les mentions inutiles).
            </td>
        </tr>
    </table>

    {{-- ══ CONDITIONNEMENT ══ --}}
    <table class="grid">
        <tr>
            <td>
                <span class="cell-title">4. Conditionnement :</span><br>
                Type : GRV / Fût / <strong>Cartons / Sacs</strong> / Autre (préciser) : ____________
            </td>
        </tr>
    </table>

    {{-- ══ QUANTITÉ + DÉTAILS ══ --}}
    <table class="grid">
        <tr>
            <td style="width:50%">
                <span class="cell-title">5. Quantité :</span><br><br>
                Quantité réelle (ou estimée) :<br>
                <strong>{{ number_format($destruction->poids_reel_kg, 2) }} kg</strong> de déchets biomédicaux<br><br>
                Poids déclaré (collecte) : {{ number_format($collecte->poids_declare_kg ?? 0, 2) }} kg<br>
                Bordereau collecte : <strong>{{ $collecte->numero_bordereau ?? '—' }}</strong>
            </td>
            <td style="width:50%">
                <span class="cell-title">6. Méthode de traitement :</span><br><br>
                @php
                    $methodes = [
                        'incineration'       => 'Incinération haute température',
                        'autoclave'          => 'Autoclave / Stérilisation',
                        'desinfection_chimique' => 'Désinfection chimique',
                        'autre'              => 'Autre méthode homologuée',
                    ];
                @endphp
                <strong>{{ $methodes[$destruction->methode] ?? $destruction->methode }}</strong><br><br>
                Conformité : <strong>{{ $destruction->conforme ? 'CONFORME ✓' : 'NON CONFORME ✗' }}</strong>
            </td>
        </tr>
    </table>

    {{-- ══ SECTION TRANSPORTEUR ══ --}}
    <table class="grid" style="margin-top:4px;">
        <tr>
            <td colspan="2" class="section-header">— À REMPLIR PAR LE COLLECTEUR-TRANSPORTEUR —</td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="cell-title">7. Collecteur-transporteur :</span><br>
                Nom : {{ $destruction->site_traitement ?? '—' }}<br>
                Adresse : —<br>
                Tél : —<br>
                Mode de transport : Véhicule homologué transport déchets dangereux
            </td>
        </tr>
    </table>

    {{-- ══ DÉCLARATION ÉMETTEUR ══ --}}
    <table class="grid" style="margin-top:4px;">
        <tr>
            <td colspan="2" class="section-header">— DÉCLARATION GÉNÉRALE DE L'ÉMETTEUR DU BORDEREAU —</td>
        </tr>
        <tr>
            <td style="width:60%">
                <span class="cell-title">8. Déclaration générale de l'émetteur du bordereau :</span><br>
                Je soussigné certifie que les renseignements portés dans les cadres ci-dessus
                sont exacts et établis de bonne foi.<br><br>
                NOM : <strong>{{ $etablissement->responsable_qhse ?? $etablissement->nom ?? '—' }}</strong>
                &nbsp;&nbsp;&nbsp; DATE : <strong>{{ \Carbon\Carbon::parse($destruction->date_destruction)->format('d/m/Y') }}</strong>
            </td>
            <td style="width:40%; text-align:center; vertical-align:middle;">
                <div style="font-size:9px; margin-bottom:4px;">Signature et cachet :</div>
                <div class="signature-box"></div>
            </td>
        </tr>
    </table>

    {{-- ══ SECTION INSTALLATION DE DESTINATION ══ --}}
    <table class="grid" style="margin-top:4px;">
        <tr>
            <td colspan="2" class="section-header">— À REMPLIR PAR L'INSTALLATION DE DESTINATION —</td>
        </tr>
        <tr>
            <td style="width:60%">
                <span class="cell-title">9. Expédition reçue par l'installation de destination :</span><br>
                Nom : <strong>{{ strtoupper($destruction->site_traitement ?? 'UNITÉ DE TRAITEMENT') }}</strong><br>
                Adresse : —<br><br>
                Quantité réelle présentée : <strong>{{ number_format($destruction->poids_reel_kg, 2) }} kg</strong><br><br>
                @if($destruction->date_reception)
                Date de réception : <strong>{{ \Carbon\Carbon::parse($destruction->date_reception)->format('d/m/Y') }}</strong>
                @endif
            </td>
            <td style="width:40%">
                Certificat n° : <strong>{{ $certificatNum }}</strong><br><br>
                Date destruction : <strong>{{ \Carbon\Carbon::parse($destruction->date_destruction)->format('d/m/Y') }}</strong><br><br>
                @if($destruction->notes)
                Notes : {{ $destruction->notes }}
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="cell-title">11. Réalisation de l'opération :</span><br>
                Description : {{ $methodes[$destruction->methode] ?? $destruction->methode }} — 
                Traitement et élimination conformes à la réglementation en vigueur sur la gestion des déchets biomédicaux.
                @if($destruction->notes)<br>Observations : {{ $destruction->notes }}@endif
            </td>
        </tr>
        <tr>
            <td style="width:60%; vertical-align:top; padding-top:6px;">
                &nbsp;
            </td>
            <td style="width:40%; text-align:center;">
                <div style="font-size:9px; margin-bottom:4px;">Signature et cachet :</div>
                <div class="signature-box"></div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Document généré le {{ now()->format('d/m/Y à H:i') }} —
        Plateforme BioMedDéchets —
        Ce bordereau atteste la destruction conforme des déchets d'activités de soins.
    </div>

</div>
</body>
</html>
