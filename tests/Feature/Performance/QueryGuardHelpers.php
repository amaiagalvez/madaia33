<?php

namespace Tests\Feature\Performance;

use Illuminate\Support\Facades\DB;

class QueryGuardHelpers
{
    /**
     * Ejecuta un callable capturando todas las queries lanzadas.
     *
     * @return array<int, array{query: string, bindings: array<mixed>, time: float}>
     */
    public static function capture(callable $flow): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $flow();

        $log = DB::getQueryLog();
        DB::disableQueryLog();

        return $log;
    }

    /**
     * Normaliza una sentencia SQL reemplazando valores por '?'
     * para poder agrupar sentencias equivalentes.
     */
    public static function normalize(string $sql): string
    {
        $sql = preg_replace("/\'[^\']*\'/", '?', $sql) ?? $sql;
        $sql = preg_replace('/\b\d+\b/', '?', $sql) ?? $sql;

        return trim(preg_replace('/\s+/', ' ', $sql) ?? $sql);
    }

    /**
     * Agrupa queries por sentencia normalizada y devuelve conteos.
     *
     * @param  array<int, array{query: string, bindings: array<mixed>, time: float}>  $log
     * @return array<string, int>
     */
    public static function groupByStatement(array $log): array
    {
        $groups = [];
        foreach ($log as $entry) {
            $key = self::normalize($entry['query']);
            $groups[$key] = ($groups[$key] ?? 0) + 1;
        }
        arsort($groups);

        return $groups;
    }

    /**
     * Aserta que el número total de queries no supera el presupuesto.
     *
     * @param  array<int, array{query: string, bindings: array<mixed>, time: float}>  $log
     */
    public static function assertMaxQueries(array $log, int $budget, string $context = ''): void
    {
        $count = count($log);
        $label = $context ? " [{$context}]" : '';

        expect($count)->toBeLessThanOrEqual(
            $budget,
            "Query budget exceeded{$label}: {$count} queries executed, expected ≤ {$budget}"
        );
    }

    /**
     * Aserta que ninguna sentencia normalizada se repite más de $maxPerStatement veces.
     *
     * @param  array<int, array{query: string, bindings: array<mixed>, time: float}>  $log
     * @param  array<int, string>  $excludePatterns  Fragmentos de SQL a excluir de la comprobación (overhead de framework)
     */
    public static function assertMaxDuplicates(array $log, int $maxPerStatement = 2, string $context = '', array $excludePatterns = []): void
    {
        $filtered = $excludePatterns
            ? array_filter($log, function (array $entry) use ($excludePatterns): bool {
                $sql = strtolower($entry['query']);
                foreach ($excludePatterns as $pattern) {
                    if (str_contains($sql, strtolower($pattern))) {
                        return false;
                    }
                }

                return true;
            })
            : $log;

        $groups = self::groupByStatement(array_values($filtered));
        $violations = array_filter($groups, fn (int $count) => $count > $maxPerStatement);

        if (empty($violations)) {
            expect(true)->toBeTrue();

            return;
        }

        $label = $context ? " [{$context}]" : '';
        $summary = collect($violations)
            ->map(fn (int $c, string $sql) => "  ({$c}x) " . substr($sql, 0, 120))
            ->implode("\n");

        expect(false)->toBeTrue(
            "Duplicate SQL detected{$label} (limit: {$maxPerStatement}x per statement):\n{$summary}"
        );
    }
}
