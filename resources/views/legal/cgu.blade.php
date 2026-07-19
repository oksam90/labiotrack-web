@extends('layouts.legal')
@section('legal_title', __('legal.nav_cgu'))
@section('legal_content')
<h1>{{ __('legal.nav_cgu') }}</h1>
<p>
    Les présentes conditions régissent l’accès et l’utilisation de la plateforme
    <strong>LaBioTrack</strong>. En se connectant, l’utilisateur les accepte.
</p>

<h2>1. Objet</h2>
<p>
    LaBioTrack est une plateforme professionnelle de traçabilité des déchets d’activités de
    soins à risques (déclaration, stockage, collecte, signature de bordereaux, destruction,
    reporting).
</p>

<h2>2. Accès et comptes</h2>
<p>
    L’accès est réservé aux utilisateurs habilités disposant d’un compte créé par un
    administrateur. Chaque utilisateur est responsable de la confidentialité de ses
    identifiants et des actions réalisées sous son compte.
</p>

<h2>3. Utilisation conforme</h2>
<ul>
    <li>Ne saisir que des données exactes et relevant de son périmètre.</li>
    <li>Ne pas tenter d’accéder à des données hors de son établissement / réseau.</li>
    <li>Ne pas perturber le fonctionnement ni la sécurité de la plateforme.</li>
</ul>

<h2>4. Valeur des signatures électroniques</h2>
<p>
    Les signatures électroniques capturées (image, horodatage, adresse IP, empreinte) valent
    preuve de la validation des bordereaux dans les conditions
    <span class="todo">[À COMPLÉTER : cadre probatoire applicable]</span>.
</p>

<h2>5. Disponibilité et responsabilité</h2>
<p>
    L’éditeur met en œuvre les moyens raisonnables pour assurer la disponibilité du service
    mais ne saurait être tenu responsable des interruptions indépendantes de sa volonté.
    <span class="todo">[À COMPLÉTER : limitations de responsabilité, SLA éventuel]</span>.
</p>

<h2>6. Données personnelles</h2>
<p>
    Le traitement des données est décrit dans la
    <a href="{{ route('legal.privacy') }}">{{ __('legal.nav_privacy') }}</a>.
</p>

<h2>7. Droit applicable</h2>
<p>
    Les présentes sont régies par le droit sénégalais.
    <span class="todo">[À COMPLÉTER : juridiction compétente]</span>.
</p>
@endsection
