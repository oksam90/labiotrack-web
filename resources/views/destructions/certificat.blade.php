@extends('layouts.app')
@section('title', 'Bordereau de destruction n° '.$destruction->certificat_numero)
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2 text-success"></i>Bordereau de destruction</h4>
        <small class="text-muted">N° {{ $destruction->certificat_numero }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('destructions.certificat.pdf', $destruction->id) }}" class="btn btn-danger btn-sm">
            <i class="bi bi-file-pdf me-1"></i>Télécharger PDF
        </a>
        <a href="{{ route('destructions.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
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
                @if($etablissement->telephone ?? null) Tél : {{ $etablissement->telephone }}<br>@endif
                @if($etablissement->email ?? null) Email : {{ $etablissement->email }}@endif
            </div>
        </div>
    </div>

    {{-- TITRE --}}
    <h3 class="text-center fw-bold text-decoration-underline mb-1" style="font-size:1.3rem;">
        Bordereau de destruction des déchets
    </h3>
    <p class="text-center text-muted mb-3" style="font-size:.82rem;">— À REMPLIR PAR L'ÉMETTEUR DU BORDEREAU —</p>
    <div class="d-inline-block border px-3 py-1 mb-3 fw-bold" style="font-size:.95rem;">
        Bordereau n° {{ $destruction->certificat_numero }}
    </div>

    {{-- EMETTEUR + DESTINATION --}}
    <table class="table table-bordered mb-3" style="font-size:.85rem;">
      <tbody>
        <tr>
          <td style="width:50%;vertical-align:top;">
            <strong>1. Émetteur du bordereau :</strong><br>
            Opérateur de gestion des déchets biomédicaux<br><br>
            Nom : <strong>{{ $etablissement->nom ?? '—' }}</strong><br>
            Adresse : {{ $etablissement->adresse ?? '—' }}<br>
            Ville : {{ $etablissement->ville ?? '—' }}<br>
            Tél : {{ $etablissement->telephone ?? '—' }}<br>
            Email : {{ $etablissement->email ?? '—' }}
          </td>
          <td style="width:50%;vertical-align:top;">
            <strong>2. Installation de destination :</strong><br><br>
            Nom : {{ $destruction->site_traitement ?? 'Unité de Traitement' }}<br>
            Personne à contacter : {{ isset($prestataire) ? ($prestataire->prenom.' '.$prestataire->nom) : '—' }}
          </td>
        </tr>
      </tbody>
    </table>

    {{-- DÉCHET + CONDITIONNEMENT --}}
    <table class="table table-bordered mb-3" style="font-size:.85rem;">
      <tbody>
        <tr>
          <td>
            <strong>3. Dénomination du déchet :</strong><br>
            Rubrique : Déchets d'Activités de Soins à Risques Infectieux (DASRI)<br>
            <strong>Déchets solides</strong> / liquides / gazeux
          </td>
        </tr>
        <tr>
          <td>
            <strong>4. Conditionnement :</strong><br>
            Type : GRV / Fût / <strong>Cartons / Sacs</strong> / Autre : ____________
          </td>
        </tr>
      </tbody>
    </table>

    {{-- QUANTITÉ + MÉTHODE --}}
    <table class="table table-bordered mb-3" style="font-size:.85rem;">
      <tbody>
        <tr>
          <td style="width:50%;vertical-align:top;">
            <strong>5. Quantité :</strong><br><br>
            Quantité réelle : <strong>{{ number_format($destruction->poids_reel_kg, 2) }} kg</strong><br>
            Poids déclaré (collecte) : {{ number_format($collecte->poids_declare_kg ?? 0, 2) }} kg<br>
            Bordereau collecte : <strong>{{ $collecte->numero_bordereau ?? '—' }}</strong>
          </td>
          <td style="width:50%;vertical-align:top;">
            <strong>6. Méthode de traitement :</strong><br><br>
            @php $m=['incineration'=>'Incinération haute température','autoclave'=>'Autoclave / Stérilisation','desinfection_chimique'=>'Désinfection chimique','autre'=>'Autre méthode homologuée']; @endphp
            <strong>{{ $m[$destruction->methode] ?? $destruction->methode }}</strong><br><br>
            Conformité :
            <span class="badge bg-{{ $destruction->conforme ? 'success' : 'danger' }}">
                {{ $destruction->conforme ? '✓ CONFORME' : '✗ NON CONFORME' }}
            </span>
          </td>
        </tr>
      </tbody>
    </table>

    {{-- SECTION TRANSPORTEUR --}}
    <div class="text-center fw-bold py-2 mb-0" style="background:#1B6B3A;color:#fff;font-size:.82rem;letter-spacing:.05em;">
        — À REMPLIR PAR LE COLLECTEUR-TRANSPORTEUR —
    </div>
    <table class="table table-bordered mb-3" style="font-size:.85rem;">
      <tbody>
        <tr>
          <td>
            <strong>7. Collecteur-transporteur :</strong><br>
            Nom : {{ $destruction->site_traitement ?? '—' }}<br>
            Mode de transport : Véhicule homologué transport déchets dangereux
          </td>
        </tr>
      </tbody>
    </table>

    {{-- DÉCLARATION EMETTEUR --}}
    <div class="text-center fw-bold py-2 mb-0" style="background:#1B6B3A;color:#fff;font-size:.82rem;letter-spacing:.05em;">
        — DÉCLARATION GÉNÉRALE DE L'ÉMETTEUR DU BORDEREAU —
    </div>
    <table class="table table-bordered mb-3" style="font-size:.85rem;">
      <tbody>
        <tr>
          <td style="width:65%;vertical-align:top;">
            <strong>8. Déclaration générale :</strong><br>
            Je soussigné certifie que les renseignements portés dans les cadres ci-dessus
            sont exacts et établis de bonne foi.<br><br>
            NOM : <strong>{{ $etablissement->responsable_qhse ?? $etablissement->nom ?? '—' }}</strong>
            &nbsp;&nbsp;&nbsp;
            DATE : <strong>{{ \Carbon\Carbon::parse($destruction->date_destruction)->format('d/m/Y') }}</strong>
          </td>
          <td style="width:35%;text-align:center;vertical-align:middle;">
            <div style="font-size:.78rem;color:#555;margin-bottom:4px;">Signature et cachet :</div>
            <div style="height:70px;border:1px solid #999;border-radius:4px;"></div>
          </td>
        </tr>
      </tbody>
    </table>

    {{-- SECTION INSTALLATION DE DESTINATION --}}
    <div class="text-center fw-bold py-2 mb-0" style="background:#1B6B3A;color:#fff;font-size:.82rem;letter-spacing:.05em;">
        — À REMPLIR PAR L'INSTALLATION DE DESTINATION —
    </div>
    <table class="table table-bordered mb-3" style="font-size:.85rem;">
      <tbody>
        <tr>
          <td style="width:60%;vertical-align:top;">
            <strong>9. Expédition reçue par l'installation de destination :</strong><br>
            Nom : <strong>{{ strtoupper($destruction->site_traitement ?? 'UNITÉ DE TRAITEMENT') }}</strong><br><br>
            Quantité réelle présentée : <strong>{{ number_format($destruction->poids_reel_kg, 2) }} kg</strong><br><br>
            @if($destruction->date_reception)
            Date de réception : <strong>{{ \Carbon\Carbon::parse($destruction->date_reception)->format('d/m/Y') }}</strong>
            @endif
          </td>
          <td style="width:40%;vertical-align:top;">
            Certificat n° : <strong>{{ $destruction->certificat_numero }}</strong><br><br>
            Date destruction : <strong>{{ \Carbon\Carbon::parse($destruction->date_destruction)->format('d/m/Y') }}</strong>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <strong>11. Réalisation de l'opération :</strong><br>
            Description : {{ $m[$destruction->methode] ?? $destruction->methode }} —
            Traitement et élimination conformes à la réglementation en vigueur sur la gestion des déchets biomédicaux.
            @if($destruction->notes)<br>Observations : {{ $destruction->notes }}@endif
          </td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td style="text-align:center;">
            <div style="font-size:.78rem;color:#555;margin-bottom:4px;">Signature et cachet :</div>
            <div style="height:70px;border:1px solid #999;border-radius:4px;"></div>
          </td>
        </tr>
      </tbody>
    </table>

    <p class="text-center text-muted mt-3" style="font-size:.72rem;">
        Document généré le {{ now()->format('d/m/Y à H:i') }} — Plateforme BioMedDéchets
    </p>
  </div>
</div>
@endsection
