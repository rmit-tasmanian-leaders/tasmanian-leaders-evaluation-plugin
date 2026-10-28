<?php
/**
 * Accessible, signed growth bar charts for program comparison PDFs.
 * Chart data is calculated upstream; this class changes presentation only.
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

    private static function num($value)
    {
        return number_format((float) $value, 2, '.', '');
    }

    private static function label_lines($label, $max_chars = 17)
    {
        $words = preg_split('/\s+/', trim((string) $label));
        $lines = [];
        $line = '';
        foreach ($words as $word) {
            $next = ($line === '') ? $word : $line . ' ' . $word;
            if ($line !== '' && strlen($next) > $max_chars) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $next;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }
        return $lines;
    }

    /**
     * Return an inline SVG using a zero-inclusive axis and role-based colours.
     * An is_primary flag takes priority over the input ordering.
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
        $scores = [];
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
                    $scores[] = (float) $growth;
                }
            }
        }
        if (!$capabilities || !$scores) {
            throw new InvalidArgumentException('No valid capability growth scores.');
        }

        $names = array_keys($capabilities);
        // Fit each section's actual growth range instead of using a symmetric axis.
        // Preserve zero while avoiding empty negative space for mostly positive data.
        $raw_min = min($scores);
        $raw_max = max($scores);
        $padding = max(2.0, ($raw_max - $raw_min) * 0.05);
        $lower_hint = $raw_min < 0 ? $raw_min - $padding : 0.0;
        $upper_hint = $raw_max > 0 ? $raw_max + $padding
            : ($raw_min == 0 ? $padding : 0.0);

        // Aim for around six human-readable intervals: 1, 2, or 5 x 10^n.
        $rough_step = max(1.0, ($upper_hint - $lower_hint) / 6.0);
        $magnitude = pow(10, floor(log10($rough_step)));
        $fraction = $rough_step / $magnitude;
        $nice_fraction = $fraction <= 1 ? 1 : ($fraction <= 2 ? 2
            : ($fraction <= 5 ? 5 : 10));
        $tick_step = $nice_fraction * $magnitude;
        $axis_min = floor($lower_hint / $tick_step) * $tick_step;
        $axis_max = ceil($upper_hint / $tick_step) * $tick_step;
        if ($axis_max <= $axis_min) {
            $axis_max = $axis_min + $tick_step;
        }

        // Keep original SVG aspect ratio to protect the five/seven-page PDF.
        $width = 1280;
        $height = 370;
        $left = 145;
        $right = 1260;
        $top = 65;
        $bottom = 280;
        $plot_width = $right - $left;
        $plot_height = $bottom - $top;
        $to_y = static function ($value) use ($top, $plot_height, $axis_min, $axis_max) {
            return $top + ($axis_max - (float) $value)
                / ($axis_max - $axis_min) * $plot_height;
        };
        $zero_y = $to_y(0);

        // The selected primary must be the darkest regardless of report sorting.
        $primary_index = 0;
        foreach ($series as $index => $row) {
            if (!empty($row['is_primary'])) {
                $primary_index = $index;
                break;
            }
        }
        $primary = '#7B0000';
        $comparisons = ['#FF1744', '#FF6A45', '#A44274'];
        $colours = [];
        $comparison_index = 0;
        foreach ($series as $index => $row) {
            $colours[$index] = $index === $primary_index
                ? $primary
                : $comparisons[$comparison_index++];
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" '
            . 'viewBox="0 0 ' . $width . ' ' . $height . '" '
            . 'width="' . $width . '" height="' . $height . '" '
            . 'role="img" aria-label="Program Growth Comparison">'
            . '<rect width="' . $width . '" height="' . $height . '" fill="#ffffff"/>';

        // The PDF engine can miss fonts declared outside an <img> SVG.
        // Make the plugin's locally bundled Mona Sans available inside
        // each self-contained chart; never fetch an external font URL.
        $font_file = dirname(__DIR__) . '/assets/fonts/MonaSans-Regular.ttf';
        if (is_readable($font_file)) {
            $font_data = file_get_contents($font_file);
            if ($font_data !== false && strlen($font_data) > 10000) {
                $svg .= '<defs><style type="text/css"><![CDATA['
                    . '@font-face{font-family:"Mona Sans";'
                    . 'src:url("data:font/truetype;base64,'
                    . base64_encode($font_data)
                    . '") format("truetype");font-weight:400;}'
                    . ']]></style></defs>';
            }
        }


        // Larger circular legend markers and Mona Sans text.
        $program_count = count($series);
        $legend_slot = $plot_width / $program_count;
        foreach ($series as $p => $row) {
            $x = $left + ($p + 0.05) * $legend_slot;
            $colour = $colours[$p];
            $label = self::xml($row['label'] ?? '');
            $svg .= '<circle cx="' . self::num($x) . '" cy="24" r="9" fill="'
                . $colour . '"/>'
                . '<text x="' . self::num($x + 17) . '" y="31" '
                . 'font-family="Mona Sans" '
                . 'font-size="22" fill="#222222">' . $label . '</text>';
        }

        // Y-axis title, gridlines, and signed tick labels.
        $svg .= '<text x="27" y="' . self::num(($top + $bottom) / 2) . '" '
            . 'transform="rotate(-90 27 ' . self::num(($top + $bottom) / 2) . ')" '
            . 'text-anchor="middle" font-size="21" '
            . 'font-family="Mona Sans" '
            . 'fill="#333333">Percentage (%)</text>';

        for ($tick = $axis_min; $tick <= $axis_max + $tick_step / 1000; $tick += $tick_step) {
            $y = $to_y($tick);
            $zero = abs($tick) < 0.000001;
            $svg .= '<line x1="' . $left . '" y1="' . self::num($y)
                . '" x2="' . $right . '" y2="' . self::num($y)
                . '" stroke="' . ($zero ? '#585858' : '#dedede')
                . '" stroke-width="' . ($zero ? 2.5 : 1.5) . '"/>'
                . '<text x="' . ($left - 17) . '" y="' . self::num($y + 6)
                . '" text-anchor="end" '
                . 'font-family="Mona Sans" '
                . 'font-size="21" fill="#444444">' . $tick . '%</text>';
        }

        // Grouped bars with a wider maximum and rounded ends.
        $slot = $plot_width / count($names);
        $bar_gap = 4;
        $bar_width = min(42, max(10, ($slot - 12) / $program_count - $bar_gap));
        $group_width = $program_count * $bar_width + ($program_count - 1) * $bar_gap;

        foreach ($names as $i => $name) {
            $middle = $left + ($i + 0.5) * $slot;
            $start = $middle - $group_width / 2;
            foreach ($series as $p => $row) {
                $value = $row['changes'][$section][$name] ?? null;
                if ($value === null) {
                    continue; // Missing is not zero.
                }
                $value = (float) $value;
                $point_y = $to_y($value);
                $rect_y = min($zero_y, $point_y);
                $rect_height = max(1, abs($zero_y - $point_y));
                $x = $start + $p * ($bar_width + $bar_gap);
                $svg .= '<rect class="growth-bar" data-growth="'
                    . self::num($value) . '" data-program="'
                    . self::xml($row['label'] ?? '') . '" x="'
                    . self::num($x) . '" y="' . self::num($rect_y)
                    . '" width="' . self::num($bar_width) . '" height="'
                    . self::num($rect_height) . '" rx="4" fill="'
                    . $colours[$p] . '"><title>'
                    . self::xml(($row['label'] ?? '') . ': ' . $name . ' '
                        . sprintf('%+.2f%%', $value)) . '</title></rect>';
            }

            foreach (array_slice(self::label_lines($name, 16), 0, 3) as $line => $text) {
                $svg .= '<text x="' . self::num($middle) . '" y="'
                    . (310 + $line * 22) . '" text-anchor="middle" '
                    . 'font-family="Mona Sans" '
                    . 'font-size="21" fill="#222222">'
                    . self::xml($text) . '</text>';
            }
        }

        return 'data:image/svg+xml;base64,' . base64_encode($svg . '</svg>');
    }
}
