<?php
/** Visual SVG contract. This test does not need a WordPress database. */
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/includes/class-comparison-chart.php';
$checks = 0;
$failures = 0;
function design_check($label, $ok) {
    global $checks, $failures;
    $checks++;
    if (!$ok) { $failures++; }
    echo ($ok ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
}
$programs = [
    ['label' => 'I-LEAD Comparison 2024', 'changes' => ['insight' => ['Self-awareness' => 20, 'Clarity of purpose' => -10]]],
    ['label' => 'I-LEAD Primary 2025', 'is_primary' => true, 'changes' => ['insight' => ['Self-awareness' => 26.33, 'Clarity of purpose' => -25]]],
    ['label' => 'I-LEAD Comparison 2025', 'changes' => ['insight' => ['Self-awareness' => 15, 'Clarity of purpose' => null]]],
];
$url = TLE_Comparison_Chart::render($programs, 'insight');
$svg = base64_decode(substr($url, strlen('data:image/svg+xml;base64,')), true);
design_check('valid svg image', $svg !== false && strpos($svg, '<svg') !== false);
design_check('unchanged svg aspect ratio for pdf fit', strpos($svg, 'viewBox="0 0 1280 370"') !== false);
design_check('Mona Sans chart label family', strpos($svg, 'Mona Sans') !== false);
design_check('circular legend markers', substr_count($svg, '<circle ') === 3);
design_check('visible percentage y-axis title', strpos($svg, 'Percentage (%)') !== false);
design_check('symmetric plus 30 tick', strpos($svg, '>30%</text>') !== false);
design_check('symmetric minus 30 tick', strpos($svg, '>-30%</text>') !== false);
design_check('primary program dark burgundy', (bool) preg_match('/data-program="I-LEAD Primary 2025"[^>]*fill="#7B0000"/', $svg));
design_check('comparison program vivid red', (bool) preg_match('/data-program="I-LEAD Comparison 2024"[^>]*fill="#FF1744"/', $svg));
design_check('rounded wider bars', strpos($svg, 'rx="4"') !== false);
design_check('missing bars not fabricated', substr_count($svg, 'class="growth-bar"') === 5);
design_check('signed values not recalculated', strpos($svg, 'data-growth="-25.00"') !== false && strpos($svg, 'data-growth="26.33"') !== false);
echo 'Checks: ' . $checks . '; failures: ' . $failures . PHP_EOL;
exit($failures ? 1 : 0);
