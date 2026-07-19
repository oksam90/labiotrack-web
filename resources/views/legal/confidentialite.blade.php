@extends('layouts.legal')
@section('legal_title', __('legal.nav_privacy'))
@section('legal_content')
<h1>{{ __('legal.nav_privacy') }}</h1>
<p>
    La présente politique décrit la manière dont la plateforme <strong>LaBioTrack</strong>
    traite les données à caractère personnel, conformément à la
    <strong>Loi n°2008-12 du 25 janvier 2008 sur la protection des données à caractère
    personnel au Sénégal</strong> (Commission de protection des données personnelles — CDP)
    et, le cas échéant, au <strong>Règlement général sur la protection des données (RGPD)</strong>.
</p>

<h2>1. Responsable de traitement</h2>
<p><span class="todo">[À COMPLÉTER : société éditrice]</span>, joignable à
    <span class="todo">[À COMPLÉTER : email]</span>.</p>

<h2>2. Données collectées</h2>
<p>La plateforme applique le principe de <strong>minimisation</strong>. Sont traitées&nbsp;:</p>
<ul>
    <li><strong>Compte utilisateur</strong> : nom, prénom, adresse email, téléphone (optionnel),
        rôle, établissement/réseau de rattachement.</li>
    <li><strong>Données de connexion</strong> : date et adresse IP de dernière connexion
        (finalité : sécurité et traçabilité des accès).</li>
    <li><strong>Données métier</strong> : déclarations de déchets, collectes, signatures
        électroniques (image de signature, horodatage, IP), rapports.</li>
</ul>
<p>Aucune donnée de santé de patient identifié n’est collectée. Aucun traceur publicitaire
   tiers n’est utilisé.</p>

<h2>3. Finalités et base légale</h2>
<ul>
    <li>Fournir le service de traçabilité (exécution du contrat / mission d’intérêt public
        de gestion des déchets d’activités de soins).</li>
    <li>Assurer la sécurité et l’intégrité (intérêt légitime).</li>
    <li>Respecter les obligations réglementaires de traçabilité (obligation légale).</li>
</ul>

<h2>4. Durée de conservation</h2>
<p>
    Les données sont conservées pour la durée nécessaire aux finalités puis archivées ou
    supprimées&nbsp;: <span class="todo">[À COMPLÉTER : durées précises, ex. bordereaux/preuves
    de signature conservés X années conformément à la réglementation déchets]</span>.
</p>

<h2>5. Destinataires et transferts</h2>
<p>
    Les données sont accessibles aux seuls utilisateurs habilités selon leur rôle et leur
    périmètre (établissement / réseau). Certains composants d’interface sont servis via un
    réseau de diffusion de contenu (CDN), ce qui peut impliquer un transfert technique de
    l’adresse IP hors du Sénégal&nbsp;: <span class="todo">[À COMPLÉTER : préciser / prévoir
    l’auto-hébergement des ressources — cf. mesures en cours]</span>.
</p>

<h2>6. Vos droits</h2>
<p>
    Conformément à la loi, vous disposez d’un droit d’accès, de rectification, d’effacement,
    de limitation, d’opposition et de portabilité de vos données. Pour les exercer, contactez
    <span class="todo">[À COMPLÉTER : email du délégué à la protection des données / contact]</span>.
    Vous pouvez introduire une réclamation auprès de la <strong>CDP</strong> (Sénégal).
</p>

<h2>7. Cookies</h2>
<p>
    La plateforme n’utilise que des <strong>cookies strictement nécessaires</strong> à son
    fonctionnement (cookie de session d’authentification, jeton anti-CSRF). Ces cookies ne
    servent pas au pistage et ne requièrent pas de consentement. Aucun cookie analytique ou
    publicitaire n’est déposé.
</p>

<h2>8. Sécurité</h2>
<p>
    Mots de passe chiffrés (bcrypt), transport chiffré (HTTPS), cloisonnement des accès par
    rôle et par établissement, journalisation des accès.
</p>
@endsection
