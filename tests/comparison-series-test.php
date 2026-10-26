<?php
/** Contract tests for full-stage, program-specific comparison series. */
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/includes/class-comparison-growth.php';
$checks = 0;
$failures = 0;
function check_series($label, $ok) {
    global $checks, $failures;
    $checks++;
    if (!$ok) { $failures++; }
    echo ($ok ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
}
function fails_series($groups, $selections) {
    try { TLE_Comparison_Growth::build_series($groups, $selections); }
    catch (InvalidArgumentException $e) { return true; }
    return false;
}
$caps = [
    'insight' => [
        'Balanced processing' => ['pre_program'=>4,'completion'=>5,'delay'=>6],
        'Clarity of purpose' => ['pre_program'=>5,'completion'=>5,'delay'=>5.5],
        'Self-awareness' => ['pre_program'=>5,'completion'=>5,'delay'=>5],
        'Strategic foresight' => ['pre_program'=>5,'completion'=>5,'delay'=>4],
        'Unmeasured' => ['pre_program'=>3,'completion'=>4],
    ],
    'influence' => [
        'Capacity for collaboration' => ['pre_program'=>4,'completion'=>5,'delay'=>5],
    ],
    'impact' => [
        'Capacity to foster belonging' => ['pre_program'=>5,'completion'=>5.5,'delay'=>5.25],
    ],
];
$groups=[];
foreach (['2023','2024','2025','2026'] as $cohort) {
    $groups[] = ['context'=>['program'=>'I-LEAD Young Professionals','cohort'=>$cohort,'program_group'=>'I-LEAD'],'capability_averages'=>$caps];
}
$select = static function($year,$primary=false) {
    return ['program'=>'I-LEAD Young Professionals','cohort'=>$year,'is_primary'=>$primary];
};
$one = TLE_Comparison_Growth::build_series($groups, [$select('2025',true),$select('2024')]);
check_series('two programs returned',count($one)===2);
check_series('oldest appears first',$one[0]['cohort']==='2024'&&$one[1]['cohort']==='2025');
check_series('primary identity retained',$one[1]['is_primary']===true);
check_series('top 3 per program',count($one[0]['top_growth']['insight'])===3);
check_series('all capabilities retained',count($one[0]['changes']['insight'])===5);
check_series('relative growth 50%', $one[0]['changes']['insight']['Balanced processing']===50.0);
check_series('decline negative 20%', $one[0]['changes']['insight']['Strategic foresight']===-20.0);
check_series('missing delayed remains null',$one[0]['changes']['insight']['Unmeasured']===null);
check_series('top sorted descending',$one[0]['top_growth']['insight'][0]['capability']==='Balanced processing');
$two = TLE_Comparison_Growth::build_series($groups, [$select('2025',true),$select('2024'),$select('2023')]);
check_series('two comparisons give top 2',count($two[0]['top_growth']['insight'])===2);
$three = TLE_Comparison_Growth::build_series($groups, [$select('2025',true),$select('2024'),$select('2023'),$select('2026')]);
check_series('three comparisons give top 1',count($three[0]['top_growth']['insight'])===1);
check_series('four programs returned',count($three)===4);
check_series('reject missing program group',fails_series($groups,[$select('2025',true),$select('2000')]));
$duplicateGroups=$groups;
$duplicateGroups[]=$groups[0];
check_series('reject ambiguous program group',fails_series($duplicateGroups,[$select('2025',true),$select('2023')]));
check_series('reject duplicate selection',fails_series($groups,[$select('2025',true),$select('2025')]));
check_series('require primary',fails_series($groups,[$select('2024'),$select('2025')]));
check_series('reject multiple primaries',fails_series($groups,[$select('2024',true),$select('2025',true)]));
check_series('require comparison',fails_series($groups,[$select('2025',true)]));
check_series('reject more than 4 programs',fails_series($groups,[$select('2023'),$select('2024'),$select('2025',true),$select('2026'),$select('2027')]));
$noDelay=$groups;
unset($noDelay[0]['capability_averages']['impact']['Capacity to foster belonging']['delay']);
unset($noDelay[0]['capability_averages']['influence']['Capacity for collaboration']['delay']);
foreach ($noDelay[0]['capability_averages']['insight'] as &$i) { unset($i['delay']); } unset($i);
check_series('reject incomplete cohort stages',fails_series($noDelay,[$select('2025',true),$select('2023')]));
echo 'Checks: '.$checks.'; failures: '.$failures.PHP_EOL;
exit($failures?1:0);
