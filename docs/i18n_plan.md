# Plan d'attaque — Internationalisation FR/EN de LaBioTrack

> **Statut** : Plan de référence à exécuter dans une nouvelle session Claude dédiée.
> **Pré-requis** : ce projet est aujourd'hui 100 % en français (vues, controllers, validation, PDFs).
> **Objectif** : ajouter le support EN sans dégrader le FR, avec sélecteur de langue
> persistant par utilisateur.

---

## 0. Stack & conventions

| Élément | Choix recommandé |
|---|---|
| Mécanisme | API Laravel natif (`__()`, `@lang`, `trans_choice()`) |
| Locales | `fr` (défaut) + `en` |
| Storage préférence utilisateur | Session + colonne `users.locale` (varchar 2, nullable, default null = FR) |
| Sélecteur | Dropdown dans le topbar (à côté de l'avatar) |
| Middleware | `SetLocaleMiddleware` (priorité : user→session→config) |
| Format | Fichiers PHP `lang/{fr,en}/*.php` (pas JSON, plus maintenable pour ce volume) |
| Package optionnel | `laravel-lang/lang` pour avoir validation/auth déjà traduits |
| Convention de clés | snake_case par domaine : `collectes.title`, `signatures.btn_sign`, `common.cancel` |

**Règle d'or** : ne jamais traduire en dur dans le code. Toujours `__('key')` ou `@lang('key')`.

---

## 1. Phases d'exécution (ordre strict)

### Phase 0 — Fondations (30-45 min, ~6 fichiers)
**Pas de regression possible**, c'est de l'infra.

1. Migration : `ALTER TABLE users ADD locale VARCHAR(2) NULLABLE`
2. `config/app.php` : confirmer `locale = 'fr'`, `fallback_locale = 'fr'`
3. `composer require laravel-lang/lang` (pose validation.php + auth.php EN/FR de qualité)
4. Créer `app/Http/Middleware/SetLocaleMiddleware.php`
   - Priorité : `auth()->user()->locale` → `session('locale')` → `config('app.locale')`
   - L'enregistrer dans `bootstrap/app.php` (alias + ajout au group `web`)
5. Créer `routes/web.php` → `POST /locale/{lang}` → contrôleur qui set session + DB si auth
6. Ajouter colonne `locale` dans `$fillable` du `User` model

**Acceptance** : un user peut basculer `/locale/en` et le voir persister entre requêtes. Pas encore d'effet visible (rien n'est encore traduit).

---

### Phase 1 — Layout et navigation (1 h, ~3 fichiers + 2 lang)
**Le plus visible — à faire en premier pour validation visuelle.**

| Fichier | Effort | Clés produites |
|---|---|---|
| `resources/views/layouts/app.blade.php` | 1 h | `nav.*` (sections sidebar), `nav.menu.*` (liens), `topbar.*` |
| `lang/fr/nav.php` + `lang/en/nav.php` | 20 min | ~25 clés |
| Sélecteur de langue dans le topbar | 15 min | (icône globe + dropdown) |

**Clés cibles** :
```
nav.section_navigation, nav.section_production, nav.section_transport,
nav.section_destruction, nav.section_conformite, nav.section_alertes,
nav.section_reseau, nav.section_administration, nav.section_platform,
nav.dashboard, nav.declarations, nav.new_declaration, nav.storage,
nav.collectes, nav.signatures, nav.destructions, nav.checklists,
nav.reports, nav.financial_analysis, nav.alerts, nav.network_dashboard,
nav.establishments, nav.comparative_analysis, nav.networks, nav.users,
nav.services, nav.containers, nav.realtime_activity, nav.logout
```

**Acceptance** : basculer FR↔EN change toute la sidebar et le topbar.

---

### Phase 2 — Auth (30 min, ~2 fichiers)
1. `resources/views/auth/login.blade.php` → ~10 clés (`auth.login_title`, `auth.email`, `auth.password`, `auth.remember`, `auth.sign_in`, `auth.session_expired`, `auth.bad_credentials`, `auth.account_disabled`)
2. `app/Http/Controllers/AuthController.php` → messages flash
3. `lang/{fr,en}/auth.php` (le package `laravel-lang/lang` fournit déjà la base)

**Acceptance** : l'écran de login bascule complètement.

---

### Phase 3 — Communs (45 min, ~1 fichier lang utilisé partout)
Créer **`lang/{fr,en}/common.php`** avec tout le vocabulaire transverse :
```
common.save, common.cancel, common.delete, common.edit, common.view,
common.back, common.confirm, common.yes, common.no, common.actions,
common.search, common.filter, common.reset, common.export, common.download,
common.print, common.previous, common.next, common.loading, common.no_data,
common.required, common.optional, common.created_at, common.updated_at,
common.status, common.active, common.inactive
```
~30 clés. **Investissement énorme pour la suite** : ces clés sont réutilisées dans ~80 % des vues.

---

### Phase 4 — Modules métier (par priorité)

Ordre = ordre de criticité métier. À chaque module : créer `lang/{fr,en}/{module}.php`, migrer la vue index + show + form + autres.

| # | Module | Fichiers vues | Effort | Clés estimées | Priorité |
|---|---|---|---|---|---|
| 4.1 | **Collectes** (cœur métier) | 4 (index, show, create, bordereau_pdf) | 1 h 30 | ~40 | 🔴 P0 |
| 4.2 | **Signatures** (cœur métier) | 4 (index, show, collectes/signature, pdf/bordereau-collecte) | 1 h 30 | ~50 | 🔴 P0 |
| 4.3 | **Déclarations** | 4 (index, show, create, edit) | 1 h | ~35 | 🟠 P1 |
| 4.4 | **Destructions** | 3 (index, show, create, certificat_pdf) | 1 h | ~30 | 🟠 P1 |
| 4.5 | **Stockage** | 2 (index, show) | 30 min | ~15 | 🟡 P2 |
| 4.6 | **Alertes** | 1 (index) | 20 min | ~10 | 🟡 P2 |
| 4.7 | **Checklists** | 3 (index, show, create, historique) | 45 min | ~25 | 🟡 P2 |
| 4.8 | **Rapports** | 2 (index, financier) + 1 PDF | 45 min | ~25 | 🟡 P2 |
| 4.9 | **Dashboard** (`dashboard/index.blade.php`) | 1 mais énorme | 1 h | ~30 | 🟠 P1 |
| 4.10 | **SuperAdmin** (réseau) | 3 (dashboard, etablissements, comparatif) | 1 h 30 | ~40 | 🟠 P1 |
| 4.11 | **Admin** (utilisateurs, structures, services, contenants) | 6 vues | 2 h | ~50 | 🟢 P3 |
| 4.12 | **Réseaux** (CRUD superadmin) | 3 (index, form, show) | 45 min | ~25 | 🟢 P3 |

**Méthode par module** :
1. Créer `lang/fr/{module}.php` ET `lang/en/{module}.php` en parallèle
2. Migrer la **vue index** d'abord (plus visible, peu de clés)
3. Migrer **show**, **form/create/edit**
4. Migrer le contrôleur (messages flash, abort messages)
5. Tester FR puis EN

---

### Phase 5 — Templates PDF (45 min, 3 fichiers)
Spécificité : DomPDF n'a pas accès à `auth()->user()->locale` directement si le job est async. **Passer explicitement la locale au job** :

```php
GenerateBordereauPdf::dispatch($signature, app()->getLocale());
// dans handle() :
app()->setLocale($this->locale);
```

Fichiers concernés :
- `resources/views/pdf/bordereau-collecte.blade.php`
- `resources/views/collectes/bordereau_pdf.blade.php`
- `resources/views/destructions/certificat_pdf.blade.php`
- `resources/views/rapports/rapport_pdf.blade.php`

---

### Phase 6 — Validation messages (30 min, 0 fichier vue)
Si `laravel-lang/lang` installé en Phase 0, **rien à faire** : les messages standard (`required`, `email`, `unique`, `min`, etc.) sont déjà bilingues.

Reste à traduire :
- Messages custom (`->validate([...], [custom messages])`) dans les contrôleurs
- Attributes custom (noms de champ français → keys)

Plan : créer `lang/{fr,en}/validation.php` qui hérite + override avec `:attribute` resolved depuis `lang/{fr,en}/attributes.php`.

---

### Phase 7 — Données dynamiques (optionnel, 30 min)
Certains contenus en base sont en français (noms d'établissements, types de déchets, services). **Ne pas traduire** : ce sont des données métier saisies par l'utilisateur. Laisser tel quel.

Exception : les **enum statuts** (`en_cours`, `signee`, `complete`, etc.) — créer un helper :
```php
// lang/{fr,en}/statuses.php
'collecte.en_cours' => 'En cours',  // FR
'collecte.en_cours' => 'In progress', // EN
```
Et dans les vues : `{{ __('statuses.collecte.' . $c->statut) }}` au lieu de `{{ ucfirst($c->statut) }}`.

---

## 2. Estimation globale

| Phase | Durée nette |
|---|---|
| 0 — Fondations | 45 min |
| 1 — Layout/nav | 1 h 30 |
| 2 — Auth | 30 min |
| 3 — Communs | 45 min |
| 4 — Modules (12 sous-modules) | **~12 h** |
| 5 — PDFs | 45 min |
| 6 — Validation | 30 min |
| 7 — Statuts | 30 min |
| **TOTAL** | **~17-18 h** de travail effectif |

Réparti sur **3 à 5 sessions Claude** pour ne pas saturer le contexte de chacune.

---

## 3. Découpage en sessions Claude recommandé

| Session | Contenu | Effort |
|---|---|---|
| **Session i18n-1** | Phase 0 + Phase 1 + Phase 2 + Phase 3 | ~3 h 30 |
| **Session i18n-2** | Phase 4.1 (Collectes) + 4.2 (Signatures) + 4.9 (Dashboard) | ~4 h |
| **Session i18n-3** | Phase 4.3 (Déclarations) + 4.4 (Destructions) + 4.5-4.8 (Stockage/Alertes/Checklists/Rapports) | ~4 h |
| **Session i18n-4** | Phase 4.10 (SuperAdmin) + 4.11 (Admin) + 4.12 (Réseaux) | ~4 h |
| **Session i18n-5** | Phase 5 (PDFs) + Phase 6 (Validation) + Phase 7 (Statuts) + tests croisés | ~2 h |

**Démarrer chaque session par un seul Read de ce fichier (`docs/i18n_plan.md`) pour reprendre le fil.**

---

## 4. Commandes utiles

```bash
# Détecter les chaînes françaises encore en dur dans les vues
grep -rEn "[a-zA-Z]" resources/views/ | grep -iE "(é|è|à|ê|ç|ï|ù)" | grep -v "__\(" | head -50

# Vérifier qu'aucune clé n'est manquante côté EN après migration
diff <(php artisan tinker --execute="print_r(array_keys(__('collectes')));") \
     <(php artisan tinker --execute="app()->setLocale('en'); print_r(array_keys(__('collectes')));")

# Compter le nombre de chaînes à traduire dans un fichier
grep -oE '>[^<]*<' resources/views/collectes/index.blade.php | wc -l
```

---

## 5. Pièges connus

1. **JS hardcodé** : `alert('Veuillez dessiner...')` dans `signature.blade.php` — passer par data-* attributes : `data-msg-empty="{{ __('signatures.empty_signature') }}"`
2. **Format de date** : `Carbon::format('d/m/Y')` reste FR par habitude — utiliser `->isoFormat('L')` qui suit la locale
3. **Format de nombre** : `number_format($x, 2, ',', ' ')` vs `'.'/','` selon locale — créer un helper `formatPoids()` qui lit la locale
4. **Cache de vues** : après chaque session i18n, `php artisan view:clear`
5. **Cache de config** : ne PAS faire `php artisan config:cache` pendant la migration (gel la locale par défaut)
6. **PDF queue** : les jobs hérités du contexte serveur, pas du user → toujours passer la locale en paramètre du job
7. **`@can`** : continue à fonctionner, pas concerné par i18n
8. **Composants Bootstrap** (badges, alerts) : juste le texte change, les classes restent

---

## 6. Definition of Done (par phase)

Pour considérer une phase terminée :
- [ ] Tous les fichiers de vue de la phase utilisent `__()` / `@lang`
- [ ] Tous les controllers de la phase utilisent `__()` pour les messages flash
- [ ] Les fichiers `lang/fr/*.php` ET `lang/en/*.php` contiennent toutes les clés
- [ ] Test manuel : basculer EN, naviguer, basculer FR, naviguer
- [ ] `php artisan view:clear && php artisan optimize:clear`
- [ ] Aucune chaîne française codée en dur restante dans les fichiers de la phase (grep)

---

## 7. Prompt de démarrage pour Session i18n-1

> Salut Claude. Je veux ajouter le support i18n FR/EN à LaBioTrack.
> Lis d'abord `docs/i18n_plan.md`. On va faire les **Phases 0, 1, 2 et 3** dans cette session.
> Pas de commit/push pendant l'implémentation — on validera tout à la fin.
> Commence par Phase 0 (Fondations).
