<?php
/**
 * Relative capability growth used by program comparison reports.
 *
 * Growth = (final - initial) / initial * 100.
 * Values may be positive, zero or negative.
 */
if (!defined('ABSPATH')) {
    exit;
}

class TLE_Comparison_Growth
{
    public static function relative_change($initial, $final)
    {
        if (!is_numeric($initial) || !is_numeric($final)) {
            return null;
        }

        $initial = (float) $initial;
        $final = (float) $final;

        if (
            !is_finite($initial) ||
            !is_finite($final) ||
            $initial <= 0 ||
            $final < 0
        ) {
            return null;
        }

        return round(
            (($final - $initial) / $initial) * 100,
            2
        );
    }

    /**
     * Each capability must contain scores for both selected stages.
     * Missing scores produce null, never a fabricated zero.
     */
    public static function section_changes(
        $capabilities,
        $from = 'pre_program',
        $to = 'delay'
    ) {
        $changes = [];

        foreach ($capabilities as $name => $stages) {
            if (!is_array($stages)) {
                $changes[$name] = null;
                continue;
            }

            $changes[$name] = self::relative_change(
                $stages[$from] ?? null,
                $stages[$to] ?? null
            );
        }

        return $changes;
    }

    /**
     * Sort by greatest signed growth, excluding unavailable values.
     * Negative values remain eligible when they are the highest
     * observed changes.
     */
    public static function top_growth($changes, $limit)
    {
        $rows = [];

        foreach ($changes as $name => $growth) {
            if (
                $growth === null ||
                !is_numeric($growth) ||
                !is_finite((float) $growth)
            ) {
                continue;
            }

            $rows[] = [
                'capability' => (string) $name,
                'growth' => (float) $growth,
            ];
        }

        usort($rows, static function ($a, $b) {
            $comparison = $b['growth'] <=> $a['growth'];

            return $comparison !== 0
                ? $comparison
                : strcmp($a['capability'], $b['capability']);
        });

        return array_slice(
            $rows,
            0,
            max(0, (int) $limit)
        );
    }

    /**
     * One comparison: top 3 per program.
     * Two comparisons: top 2 per program.
     * Three comparisons: top 1 per program.
     */
    public static function summary_limit($comparison_count)
    {
        $comparison_count = (int) $comparison_count;

        if ($comparison_count < 1 || $comparison_count > 3) {
            return 0;
        }

        return 4 - $comparison_count;
    }
}