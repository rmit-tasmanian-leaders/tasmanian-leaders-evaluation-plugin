<?php
/** Regression checks for dynamic, zero-inclusive comparison-chart axes. */
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/includes/class-comparison-chart.php';
$checks = 0;
$failures = 0;
function axis_check($label, $condition) {
    global $checks, $failures;
    $checks++;
    if (!$condition) { $failures++; }
    echo ($condition ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
}
function axis_fixture($a, $b) {
    $data = [
        ['label' => 'Comparison', 'changes' => ['insight' => ['Awareness' => $a]]],
        ['label' => 'Primary', 'is_primary' => true, 'changes' => ['insight' => ['Awareness' => $b]]],
    ];
    $uri = TLE_Comparison_Chart::render($data, 'insight');
    $svg = base64_decode(substr($uri, strlen('data:image/svg+xml;base64,')), true);
    if (!is_string($svg)) { throw new RuntimeException('Invalid SVG data'); }
    preg_match_all('/>(-?[0-9]+(?:\.[0-9]+)?)%<\/text>/', $svg, $m);
    $ticks = array_map('floatval', $m[1]);
    return [$ticks, $svg];
}
list($mixed, $mixed_svg) = axis_fixture(-5, 26);
axis_check('mixed chart minimizes dead space below zero', min($mixed) === -10.0);
axis_check('mixed chart fits highest positive value', max($mixed) === 30.0);
axis_check('mixed chart retains visible zero', in_array(0.0, $mixed, true));
axis_check('mixed chart keeps negative bar intact', strpos($mixed_svg, 'data-growth="-5.00"') !== false);
list($positive, $positive_svg) = axis_fixture(5, 16);
axis_check('all positive lower bound is exactly zero', min($positive) === 0.0);
axis_check('all positive upper bound includes all values', max($positive) >= 16);
list($negative, $negative_svg) = axis_fixture(-5, -16);
axis_check('all negative upper bound is exactly zero', max($negative) === 0.0);
axis_check('all negative lower bound includes all values', min($negative) <= -16);
list($zeros, $zeros_svg) = axis_fixture(0, 0);
axis_check('zero-only chart has valid tick range', min($zeros) === 0.0 && max($zeros) > 0);
axis_check('zero-only growth values preserved', substr_count($zeros_svg, 'data-growth="0.00"') === 2);
list($large, $large_svg) = axis_fixture(-25, 26.33);
axis_check('large mixed values rounded outward', min($large) === -30.0 && max($large) === 30.0);
axis_check('chart SVG dimensions preserved', strpos($large_svg, 'viewBox="0 0 1280 370"') !== false);
echo 'Checks: ' . $checks . '; failures: ' . $failures . PHP_EOL;
exit($failures ? 1 : 0);