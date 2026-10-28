<?php
/**
 * Standalone comparison-growth tests.
 * No WordPress database or Gravity Forms credentials required.
 */

define('ABSPATH', __DIR__);

require dirname(__DIR__) .
    '/includes/class-comparison-growth.php';

$checks = 0;
$failures = 0;

function growth_check($label, $condition)
{
    global $checks, $failures;

    $checks++;

    if (!$condition) {
        $failures++;
    }

    echo ($condition ? 'PASS ' : 'FAIL ')
        . $label . PHP_EOL;
}

growth_check(
    '50 to 100 is 100 percent growth',
    TLE_Comparison_Growth::relative_change(50, 100) === 100.0
);

growth_check(
    '50 to 75 is 50 percent growth',
    TLE_Comparison_Growth::relative_change(50, 75) === 50.0
);

growth_check(
    '80 to 60 is negative 25 percent',
    TLE_Comparison_Growth::relative_change(80, 60) === -25.0
);

growth_check(
    'unchanged values give zero',
    TLE_Comparison_Growth::relative_change(50, 50) === 0.0
);

growth_check(
    '50 to zero is negative 100 percent',
    TLE_Comparison_Growth::relative_change(50, 0) === -100.0
);

growth_check(
    'zero baseline is undefined',
    TLE_Comparison_Growth::relative_change(0, 50) === null
);

growth_check(
    'zero to zero is undefined',
    TLE_Comparison_Growth::relative_change(0, 0) === null
);

growth_check(
    'missing scores remain unavailable',
    TLE_Comparison_Growth::relative_change(null, 50) === null
);

growth_check(
    'negative baseline rejected',
    TLE_Comparison_Growth::relative_change(-10, 20) === null
);

growth_check(
    'infinite scores rejected',
    TLE_Comparison_Growth::relative_change(INF, 20) === null
);

$capabilities = [
    'Self-awareness' => [
        'pre_program' => 3.5,
        'completion' => 5.0,
        'delay' => 7.0,
    ],
    'Collaboration' => [
        'pre_program' => 4.0,
        'completion' => 5.0,
        'delay' => 6.0,
    ],
    'Strategic foresight' => [
        'pre_program' => 4.0,
        'completion' => 3.5,
        'delay' => 3.0,
    ],
    'Missing delayed result' => [
        'pre_program' => 4.0,
    ],
];

$changes = TLE_Comparison_Growth::section_changes(
    $capabilities
);

growth_check(
    'uses Pre-program to Delayed',
    $changes['Self-awareness'] === 100.0
);

growth_check(
    'calculates intermediate growth',
    $changes['Collaboration'] === 50.0
);

growth_check(
    'negative capability growth preserved',
    $changes['Strategic foresight'] === -25.0
);

growth_check(
    'missing delayed stage remains null',
    $changes['Missing delayed result'] === null
);

$top = TLE_Comparison_Growth::top_growth(
    $changes,
    2
);

growth_check(
    'top capabilities ordered by relative growth',
    count($top) === 2 &&
    $top[0]['capability'] === 'Self-awareness' &&
    $top[1]['capability'] === 'Collaboration'
);

growth_check(
    'unavailable values excluded from top growth',
    count(TLE_Comparison_Growth::top_growth($changes, 4)) === 3
);

growth_check(
    'one comparison uses top three',
    TLE_Comparison_Growth::summary_limit(1) === 3
);

growth_check(
    'two comparisons use top two',
    TLE_Comparison_Growth::summary_limit(2) === 2
);

growth_check(
    'three comparisons use top one',
    TLE_Comparison_Growth::summary_limit(3) === 1
);

growth_check(
    'no comparison retains legacy summary mode',
    TLE_Comparison_Growth::summary_limit(0) === 0
);

echo 'Checks: ' . $checks .
    '; failures: ' . $failures . PHP_EOL;

exit($failures ? 1 : 0);