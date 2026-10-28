<?php
/**
 * Program-by-program relative growth summaries for comparison PDFs.
 * Only top-growth items from validated TLE_Comparison_Growth::build_series()
 * are rendered. Never invent a missing score.
 */
if (!defined('ABSPATH')) {
    exit;
}

class TLE_Comparison_Summary
{
    public static function field_key($value)
    {
        return rawurlencode((string) $value);
    }

    private static function safe($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function automatic_text($capability, $growth)
    {
        $amount = number_format(abs((float) $growth), 2, '.', '');
        if ($growth > 0) {
            return $capability . ' improved by ' . $amount .
                '% from Pre-program to Delayed.';
        }
        if ($growth < 0) {
            return $capability . ' declined by ' . $amount .
                '% from Pre-program to Delayed.';
        }
        return $capability . ' was unchanged from Pre-program to Delayed.';
    }

    /**
     * Optional overrides are keyed by section and URL-encoded program/score
     * names, e.g. growth_summary_text[insight][PROGRAM_KEY][CAPABILITY_KEY].
     * A missing override means use the automatic description.
     */
    public static function render_section(array $series, $section, $overrides = [])
    {
        if (!in_array($section, ['insight', 'influence', 'impact'], true)) {
            throw new InvalidArgumentException('Unknown growth summary section.');
        }
        if (count($series) < 2 || count($series) > 4) {
            throw new InvalidArgumentException('Expected 2 to 4 programs.');
        }
        $items_per_program = 5 - count($series); // 2/3/4 programs -> 3/2/1 items
        $html = '<div class="tle-growth-program-summaries" style="font-size:10px;line-height:1.4;">';
        foreach ($series as $entry) {
            $program = (string) ($entry['program'] ?? '');
            $cohort = (string) ($entry['cohort'] ?? '');
            $name = (string) ($entry['label'] ?? ($program . ' ' . $cohort));
            $top = $entry['top_growth'][$section] ?? null;
            if (!is_array($top) || !$top) {
                throw new InvalidArgumentException('Missing top growth for selected program.');
            }
            $html .= '<div class="tle-growth-program" style="margin:0 0 7px;">';
            $html .= '<div class="tle-growth-program-name" style="font-weight:bold;margin:0 0 3px;">'
                . self::safe($name) . '</div>';
            $program_key = self::field_key($program . '|' . $cohort);
            foreach (array_slice($top, 0, $items_per_program) as $item) {
                $capability = (string) ($item['capability'] ?? '');
                $growth = $item['growth'] ?? null;
                if ($capability === '' || !is_numeric($growth) ||
                    !is_finite((float) $growth)) {
                    throw new InvalidArgumentException('Invalid top growth item.');
                }
                $capability_key = self::field_key($capability);
                $custom = $overrides[$section][$program_key][$capability_key] ?? null;
                if (!is_string($custom)) {
                    $custom = null;
                }
                $text = $custom !== null && trim($custom) !== ''
                    ? (function_exists('mb_substr')
                        ? mb_substr(trim($custom), 0, 500, 'UTF-8')
                        : substr(trim($custom), 0, 500))
                    : self::automatic_text($capability, $growth);
                $html .= '<div class="tle-growth-summary-line" style="margin:0 0 2px;">'
                    . self::safe($text) . '</div>';
            }
            $html .= '</div>';
        }
        return $html . '</div>';
    }
}