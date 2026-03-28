<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Service centralisé pour la gestion du cache applicatif.
 *
 * Convention de clés :
 *   dashboard:{etabId}:{mois}       → KPIs + métriques du dashboard
 *   rapport:{etabId}:{debut}:{fin}  → données collectées pour un rapport
 *   superadmin:kpis:{mois}          → KPIs réseau global
 *   superadmin:etab:{id}:{mois}     → KPIs d'un établissement
 *   financier:{etabId}:{mois}       → analyse financière
 */
class CacheService
{
    /** TTL par défaut : 5 minutes (données KPI rafraîchies fréquemment) */
    public const TTL_DASHBOARD = 300;

    /** TTL rapports : 15 minutes (données historiques, changent peu) */
    public const TTL_RAPPORT = 900;

    /** TTL analyse financière : 10 minutes */
    public const TTL_FINANCIER = 600;

    // ── Clés ──────────────────────────────────────────────────

    public static function dashboardKey(int $etabId, string $mois): string
    {
        return "dashboard:{$etabId}:{$mois}";
    }

    public static function rapportKey(?int $etabId, string $debut, string $fin): string
    {
        return "rapport:" . ($etabId ?? 'global') . ":{$debut}:{$fin}";
    }

    public static function superadminKpisKey(string $mois): string
    {
        return "superadmin:kpis:{$mois}";
    }

    public static function superadminEtabKey(int $etabId, string $mois): string
    {
        return "superadmin:etab:{$etabId}:{$mois}";
    }

    public static function financierKey(?int $etabId, string $mois): string
    {
        return "financier:" . ($etabId ?? 'global') . ":{$mois}";
    }

    // ── Invalidation ──────────────────────────────────────────

    /**
     * Invalide tous les caches liés à un établissement.
     * Appelé par les Observers lors de create/update/delete.
     */
    public static function invalidateForEtablissement(?int $etabId): void
    {
        if (! $etabId) return;

        $mois = now()->format('Y-m');

        // Dashboard de l'établissement
        Cache::forget(self::dashboardKey($etabId, $mois));

        // KPIs superadmin (réseau global impacté)
        Cache::forget(self::superadminKpisKey($mois));

        // KPIs superadmin pour cet établissement
        Cache::forget(self::superadminEtabKey($etabId, $mois));

        // Analyse financière
        Cache::forget(self::financierKey($etabId, $mois));
        Cache::forget(self::financierKey(null, $mois)); // global

        // Rapports du mois courant (début/fin classiques)
        $debutMois = now()->startOfMonth()->toDateString();
        $finMois   = now()->endOfMonth()->toDateString();
        Cache::forget(self::rapportKey($etabId, $debutMois, $finMois));
        Cache::forget(self::rapportKey(null, $debutMois, $finMois));
    }

    /**
     * Invalide tout le cache applicatif (maintenance, migration, etc.)
     */
    public static function invalidateAll(): void
    {
        Cache::flush();
    }
}
