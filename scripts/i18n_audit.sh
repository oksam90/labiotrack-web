#!/usr/bin/env bash
# scripts/i18n_audit.sh
#
# Audit i18n — outils de diagnostic pour la migration FR/EN.
#
# Trois contrôles :
#   1. Détection des chaînes françaises encore codées en dur
#   2. Diff de clés entre lang/fr et lang/en (parité)
#   3. Comptage des chaînes à traduire par fichier (priorisation)
#
# Usage :
#   ./scripts/i18n_audit.sh              # tous les contrôles
#   ./scripts/i18n_audit.sh hardcoded    # 1 uniquement
#   ./scripts/i18n_audit.sh parity       # 2 uniquement
#   ./scripts/i18n_audit.sh count        # 3 uniquement

set -e
cd "$(dirname "$0")/.."

MODE="${1:-all}"

# ─────────────────────────────────────────────────────────────────────
# 1. CHAÎNES FRANÇAISES CODÉES EN DUR
# ─────────────────────────────────────────────────────────────────────
# Cible : lignes contenant un caractère accentué (é è à ê ç ï ù ô î â)
# qui ne sont PAS dans un appel __() ou @lang().
hardcoded() {
    echo "═══════════════════════════════════════════════════════════════"
    echo "  1. Chaînes françaises codées en dur (par fichier)"
    echo "═══════════════════════════════════════════════════════════════"
    echo
    {
        find resources/views -type f -name '*.blade.php' \
        | while read -r f; do
            count=$(grep -cE '[éèàêçïùôîâû]' "$f" 2>/dev/null \
                | awk -F: '{print $1}')
            in_helper=$(grep -cE "(__\(|@lang\()" "$f" 2>/dev/null || echo 0)
            # Lignes avec accent, hors lignes purement dans __()
            net=$(grep -nE '[éèàêçïùôîâû]' "$f" 2>/dev/null \
                | grep -vE "(__\(|@lang\()" \
                | wc -l | tr -d ' ')
            if [ "$net" -gt 0 ]; then
                printf "  %4d lignes  %s\n" "$net" "$f"
            fi
        done
    } | sort -rn | head -40
    echo
    echo "  → Top 40 fichiers avec accents non traduits."
    echo "  → Pour voir le détail d'un fichier :"
    echo "    grep -nE '[éèàêçïùôîâû]' <file> | grep -vE \"(__\(|@lang\()\""
    echo
}

# ─────────────────────────────────────────────────────────────────────
# 2. PARITÉ DE CLÉS FR ↔ EN
# ─────────────────────────────────────────────────────────────────────
parity() {
    echo "═══════════════════════════════════════════════════════════════"
    echo "  2. Parité de clés entre lang/fr/*.php et lang/en/*.php"
    echo "═══════════════════════════════════════════════════════════════"
    echo

    if [ ! -d lang/fr ] || [ ! -d lang/en ]; then
        echo "  /!\\ Répertoires lang/fr ou lang/en absents"
        return
    fi

    for fr_file in lang/fr/*.php; do
        name=$(basename "$fr_file")
        en_file="lang/en/$name"

        if [ ! -f "$en_file" ]; then
            echo "  [MANQUANT]  lang/en/$name (présent en FR uniquement)"
            continue
        fi

        # Extrait toutes les clés top-level via php -r
        fr_keys=$(php -r "\$a = include '$fr_file'; foreach (array_keys(\$a) as \$k) echo \$k . PHP_EOL;" 2>/dev/null | sort)
        en_keys=$(php -r "\$a = include '$en_file'; foreach (array_keys(\$a) as \$k) echo \$k . PHP_EOL;" 2>/dev/null | sort)

        missing_in_en=$(comm -23 <(echo "$fr_keys") <(echo "$en_keys"))
        missing_in_fr=$(comm -13 <(echo "$fr_keys") <(echo "$en_keys"))

        if [ -z "$missing_in_en" ] && [ -z "$missing_in_fr" ]; then
            fr_n=$(echo "$fr_keys" | wc -l | tr -d ' ')
            printf "  [OK]        %-20s %d clés synchronisées\n" "$name" "$fr_n"
        else
            echo "  [DESYNC]    $name :"
            if [ -n "$missing_in_en" ]; then
                echo "$missing_in_en" | sed 's/^/                manquant en EN : /'
            fi
            if [ -n "$missing_in_fr" ]; then
                echo "$missing_in_fr" | sed 's/^/                manquant en FR : /'
            fi
        fi
    done

    # Inverse : fichiers présents en EN seulement
    for en_file in lang/en/*.php; do
        name=$(basename "$en_file")
        if [ ! -f "lang/fr/$name" ]; then
            echo "  [MANQUANT]  lang/fr/$name (présent en EN uniquement)"
        fi
    done
    echo
}

# ─────────────────────────────────────────────────────────────────────
# 3. COMPTAGE DES CHAÎNES À TRADUIRE PAR FICHIER (PRIORISATION)
# ─────────────────────────────────────────────────────────────────────
# Estimation : nombre de blocs `>...<` (texte HTML), plus
# `placeholder="..."`, `title="..."`, `value="..."` non vides.
count() {
    echo "═══════════════════════════════════════════════════════════════"
    echo "  3. Estimation du volume de traduction par vue"
    echo "═══════════════════════════════════════════════════════════════"
    echo

    {
        find resources/views -type f -name '*.blade.php' \
        | while read -r f; do
            # Texte entre balises HTML (>texte<), excluant {{ }} purs et vide
            txt=$(grep -oE '>[^<>{][^<>]{2,}<' "$f" 2>/dev/null | wc -l | tr -d ' \n')
            # Attributs textuels
            attr=$(grep -oE '(placeholder|title|alt|aria-label)="[^"]+"' "$f" 2>/dev/null | wc -l | tr -d ' \n')
            txt=${txt:-0}; attr=${attr:-0}
            total=$((txt + attr))
            if [ "$total" -gt 0 ]; then
                printf "  %4d chaînes  %s\n" "$total" "$f"
            fi
        done
    } | sort -rn | head -30
    echo
    echo "  → Top 30 vues par volume estimé. Aide à prioriser les sessions."
    echo
}

case "$MODE" in
    hardcoded)  hardcoded ;;
    parity)     parity ;;
    count)      count ;;
    all|*)      hardcoded; parity; count ;;
esac
