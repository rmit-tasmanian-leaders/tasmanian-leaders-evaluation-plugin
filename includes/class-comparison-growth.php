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

    /**
     * Build validated, cohort-specific growth series for comparison reports.
     * Input groups must come from the full (unfiltered) reporting service.
     * Missing or ambiguous cohorts must never be merged silently.
     *
     * Each selection: program, cohort, is_primary.
     */
    public static function build_series(array $groups, array $selections)
    {
        if (count($selections) < 2 || count($selections) > 4) {
            throw new InvalidArgumentException(
                'Select the primary program and one to three comparisons.'
            );
        }

        $seen = [];
        $primary_count = 0;
        $series = [];

        foreach ($selections as $selection) {
            if (!is_array($selection)) {
                throw new InvalidArgumentException('Invalid program selection.');
            }

            $program = trim((string) ($selection['program'] ?? ''));
            $cohort = trim((string) ($selection['cohort'] ?? ''));
            $is_primary = !empty($selection['is_primary']);

            if ($program === '' || $cohort === '') {
                throw new InvalidArgumentException('Program and year are required.');
            }

            $key = $program . '|' . $cohort;
            if (isset($seen[$key])) {
                throw new InvalidArgumentException('Duplicate program and year: ' . $key);
            }
            $seen[$key] = true;
            if ($is_primary) {
                $primary_count++;
            }

            $matches = [];
            foreach ($groups as $group) {
                $context = $group['context'] ?? [];
                if (
                    ($context['program'] ?? '') === $program &&
                    (string) ($context['cohort'] ?? '') === $cohort
                ) {
                    $matches[] = $group;
                }
            }

            if (count($matches) !== 1) {
                throw new InvalidArgumentException(
                    'Expected exactly one reporting group for ' . $key .
                    '; found ' . count($matches) . '.'
                );
            }

            $capabilities = $matches[0]['capability_averages'] ?? [];
            if (!is_array($capabilities)) {
                throw new InvalidArgumentException('Invalid capability data for ' . $key);
            }

            // A comparison requires scored results at all three stages.
            foreach (['pre_program', 'completion', 'delay'] as $stage) {
                $found_score = false;
                foreach ($capabilities as $section_caps) {
                    if (!is_array($section_caps)) {
                        continue;
                    }
                    foreach ($section_caps as $values) {
                        $score = is_array($values) ? ($values[$stage] ?? null) : null;
                        if (is_numeric($score) && is_finite((float) $score)) {
                            $found_score = true;
                            break 2;
                        }
                    }
                }
                if (!$found_score) {
                    throw new InvalidArgumentException(
                        'Missing usable ' . $stage . ' scores for ' . $key . '.'
                    );
                }
            }

            $changes = [];
            foreach (['insight', 'influence', 'impact'] as $section) {
                $section_caps = $capabilities[$section] ?? null;
                if (!is_array($section_caps)) {
                    throw new InvalidArgumentException(
                        'Missing ' . $section . ' capabilities for ' . $key . '.'
                    );
                }
                $changes[$section] = self::section_changes($section_caps);
                if (count(self::top_growth($changes[$section], 1)) === 0) {
                    throw new InvalidArgumentException(
                        'No valid ' . $section . ' growth scores for ' . $key . '.'
                    );
                }
            }

            $series[] = [
                'program' => $program,
                'cohort' => $cohort,
                'label' => $program . ' ' . $cohort,
                'is_primary' => $is_primary,
                'changes' => $changes,
            ];
        }

        if ($primary_count !== 1) {
            throw new InvalidArgumentException('Exactly one primary program is required.');
        }

        // Match the mock-up's oldest-to-newest layout; primary last on ties.
        usort($series, static function ($a, $b) {
            if (is_numeric($a['cohort']) && is_numeric($b['cohort'])) {
                $year_order = (int) $a['cohort'] <=> (int) $b['cohort'];
                if ($year_order !== 0) {
                    return $year_order;
                }
            } else {
                $year_order = strcmp($a['cohort'], $b['cohort']);
                if ($year_order !== 0) {
                    return $year_order;
                }
            }
            if ($a['is_primary'] !== $b['is_primary']) {
                return $a['is_primary'] ? 1 : -1;
            }
            return strcmp($a['program'], $b['program']);
        });

        $limit = self::summary_limit(count($selections) - 1);
        foreach ($series as &$entry) {
            $entry['top_growth'] = [];
            foreach ($entry['changes'] as $section => $changes) {
                $entry['top_growth'][$section] = self::top_growth($changes, $limit);
            }
        }
        unset($entry);

        return $series;
    }
}
