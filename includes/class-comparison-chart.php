<?php
/**
 * Signed, program-specific growth charts for comparison PDFs.
 * Input: rows created by TLE_Comparison_Growth::build_series().
 */
if (!defined('ABSPATH')) {
    exit;
}

class TLE_Comparison_Chart
{
    private static function xml($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private static function format_number($value)
    {
        return number_format((float) $value, 2, '.', '');
    }

    private static function nice_step($raw)
    {
        $raw = max(0.001, (float) $raw);
        $power = pow(10, floor(log10($raw)));
        $fraction = $raw / $power;

        if ($fraction <= 1) {
            return $power;
        }
        if ($fraction <= 2) {
            return 2 * $power;
        }
        if ($fraction <= 5) {
            return 5 * $power;
        }

        return 10 * $power;
    }

    private static function label_lines($label, $max_chars = 20)
    {
        $words = preg_split('/\s+/', trim((string) $label));
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if ($current !== '' && strlen($candidate) > $max_chars) {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }
        return $lines;
    }

    /**
     * Produce a base64-encoded inline SVG with a signed zero baseline.
     * Bars are grouped by capability and coloured by selected program.
     */
    public static function render(array $series, $section, $primary_colour = '#83000b')
    {
        if (!in_array($section, ['insight', 'influence', 'impact'], true)) {
            throw new InvalidArgumentException('Unknown comparison section.');
        }
        if (count($series) < 2 || count($series) > 4) {
            throw new InvalidArgumentException('Expected two to four selected programs.');
        }

        $capabilities = [];
        $values = [];
        foreach ($series as $row) {
            if (!is_array($row) || !is_array($row['changes'][$section] ?? null)) {
                throw new InvalidArgumentException('Missing program growth series.');
            }
            foreach ($row['changes'][$section] as $capability => $growth) {
                $capabilities[(string) $capability] = true;
                if ($growth !== null) {
                    if (!is_numeric($growth) || !is_finite((float) $growth)) {
                        throw new InvalidArgumentException('Invalid growth score.');
                    }
                    $values[] = (float) $growth;
                }
            }
        }
        if (!$capabilities || !$values) {
            throw new InvalidArgumentException('No valid capability growth scores.');
        }

        $names = array_keys($capabilities);
        $minimum = min(0.0, min($values));
        $maximum = max(0.0, max($values));
        $step = self::nice_step(max($maximum - $minimum, 1) / 5);
        $axis_min = floor($minimum / $step) * $step;
        $axis_max = ceil($maximum / $step) * $step;
        if ($axis_min === $axis_max) {
            $axis_min -= $step;
            $axis_max += $step;
        }

        $width = 1280;
        $height = 370;
        $left = 80;
        $right = 1260;
        $top = 55;
        $bottom = 265;
        $plot_width = $right - $left;
        $plot_height = $bottom - $top;
        $span = $axis_max - $axis_min;
        $to_y = static function ($value) use ($bottom, $plot_height, $axis_min, $span) {
            return $bottom - (($value - $axis_min) / $span) * $plot_height;
        };
        $zero_y = $to_y(0);

        $palette = ['#83000b', '#ff003d', '#a44274', '#77458b'];
        if (is_string($primary_colour) &&
            preg_match('/^#[0-9a-fA-F]{6}$/', $primary_colour)
        ) {
            $palette[0] = $primary_colour;
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" '
            . 'viewBox="0 0 1280 370" width="1280" height="370" '
            . 'role="img" aria-label="Program Growth Comparison">'
            . '<rect width="1280" height="370" fill="#fff"/>';

        $program_count = count($series);
        $legend_slot = $plot_width / $program_count;
        foreach ($series as $p => $row) {
            $label = (string) ($row['label'] ?? '');
            $x = $left + $p * $legend_slot;
            $colour = $palette[$p];
            $svg .= '<rect x="' . self::format_number($x) . '" y="15" '
                . 'width="13" height="13" fill="' . $colour . '"/>'
                . '<text x="' . self::format_number($x + 19) . '" y="26" '
                . 'font-family="DejaVu Sans,sans-serif" font-size="12" '
                . 'fill="#333">' . self::xml($label) . '</text>';
        }

        $tick_count = (int) round($span / $step);
        for ($i = 0; $i <= $tick_count; $i++) {
            $tick = $axis_min + $i * $step;
            $y = $to_y($tick);
            $is_zero = abs($tick) < max(1e-9, $step / 10000);
            $svg .= '<line x1="' . $left . '" y1="' . self::format_number($y)
                . '" x2="' . $right . '" y2="' . self::format_number($y)
                . '" stroke="' . ($is_zero ? '#555' : '#e0e0e0')
                . '" stroke-width="' . ($is_zero ? 2 : 1) . '"/>'
                . '<text x="70" y="' . self::format_number($y + 4)
                . '" text-anchor="end" font-family="DejaVu Sans,sans-serif"'
                . ' font-size="12" fill="#555">'
                . self::xml(number_format($tick, $step < 1 ? 1 : 0) . '%')
                . '</text>';
        }

        $slot = $plot_width / count($names);
        $bar_gap = 3;
        $bar_width = min(27, max(8, ($slot - 15) / $program_count - $bar_gap));
        $group_width = $program_count * $bar_width +
            ($program_count - 1) * $bar_gap;

        foreach ($names as $c => $name) {
            $mid = $left + ($c + 0.5) * $slot;
            $start_x = $mid - $group_width / 2;

            foreach ($series as $p => $row) {
                $value = $row['changes'][$section][$name] ?? null;
                if ($value === null) {
                    continue; // Unavailable is never displayed as zero.
                }
                $value = (float) $value;
                $val_y = $to_y($value);
                $bar_top = min($zero_y, $val_y);
                $bar_height = abs($zero_y - $val_y);
                $x = $start_x + $p * ($bar_width + $bar_gap);

                $svg .= '<rect class="growth-bar" data-growth="'
                    . self::format_number($value) . '" data-program="'
                    . self::xml($row['label'] ?? '') . '" x="'
                    . self::format_number($x) . '" y="'
                    . self::format_number($bar_top) . '" width="'
                    . self::format_number($bar_width) . '" height="'
                    . self::format_number(max(1, $bar_height))
                    . '" fill="' . $palette[$p] . '">'
                    . '<title>' . self::xml(($row['label'] ?? '') . ': '
                        . $name . ' ' . sprintf('%+.2f%%', $value))
                    . '</title></rect>';
            }

            $lines = self::label_lines($name, 20);
            foreach (array_slice($lines, 0, 3) as $i => $line) {
                $svg .= '<text x="' . self::format_number($mid) . '" y="'
                    . (293 + $i * 16) . '" text-anchor="middle" '
                    . 'font-family="DejaVu Sans,sans-serif" font-size="12" '
                    . 'fill="#222">' . self::xml($line) . '</text>';
            }
        }

        $svg .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
