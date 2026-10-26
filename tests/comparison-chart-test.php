<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__).'/includes/class-comparison-chart.php';
$checks=0;$fails=0;
function expect($title,$passed){global $checks,$fails;$checks++; if(!$passed)$fails++; echo ($passed?'PASS ':'FAIL ').$title.PHP_EOL;}
$labels=['2024','2025'];
$series=[];
foreach($labels as $i=>$year){$series[]=['label'=>'I-LEAD Example '.$year, 'changes'=>[
'insight'=>['Self-awareness'=>$i===0?20:-25,'Clarity of purpose'=>$i===0?null:5,'Capacity for collaboration'=>$i===0?0:10],
'influence'=>['Tolerance for ambiguity'=>-3],
'impact'=>['Place-attachment'=>2]
]];}
$encoded= TLE_Comparison_Chart::render($series,'insight');
expect('SVG image URL returned',str_starts_with($encoded,'data:image/svg+xml;base64,'));
$xml=base64_decode(substr($encoded,strlen('data:image/svg+xml;base64,')));
expect('valid non-empty SVG',$xml!==false&&strpos($xml,'<svg')!==false);
expect('SVG closes correctly',str_contains($xml,'</svg>'));
expect('all capability labels included',str_contains($xml,'Self-awareness')&&str_contains($xml,'Clarity of purpose')&&str_contains($xml,'Capacity for'));
expect('program legend kept',str_contains($xml,'Example 2024')&&str_contains($xml,'Example 2025'));
expect('positive 20 percent bar',str_contains($xml,'data-growth="20.00"'));
expect('negative 25 percent bar',str_contains($xml,'data-growth="-25.00"'));
expect('zero bar',str_contains($xml,'data-growth="0.00"'));
expect('missing value not fabricated',substr_count($xml,'class="growth-bar"')===5);
expect('zero baseline present',str_contains($xml,'>0%</text>'));
expect('negative ticks visible',str_contains($xml,'>-30%</text>')||str_contains($xml,'>-25%</text>'));
expect('positive ticks visible',str_contains($xml,'>20%</text>')||str_contains($xml,'>25%</text>'));
$more=$series;
$more[]=['label'=>'Third 2025','changes'=>$series[0]['changes']];
$more[]=['label'=>'Fourth 2025','changes'=>$series[0]['changes']];
expect('four programs supported',str_contains(base64_decode(substr(TLE_Comparison_Chart::render($more,'insight'),strlen('data:image/svg+xml;base64,'))),'Fourth 2025'));
try{TLE_Comparison_Chart::render([$series[0]],'insight');$invalid=false;}catch(InvalidArgumentException $e){$invalid=true;}
expect('one program rejected',$invalid);
try{TLE_Comparison_Chart::render($series,'bogus');$invalid=false;}catch(InvalidArgumentException $e){$invalid=true;}
expect('unknown section rejected',$invalid);
$bad=$series;$bad[1]['changes']['insight']['Self-awareness']='nan';
try{TLE_Comparison_Chart::render($bad,'insight');$invalid=false;}catch(InvalidArgumentException $e){$invalid=true;}
expect('non-numeric growth rejected',$invalid);
$safe=$series;$safe[0]['label']='<script>x</script>';
$escape=base64_decode(substr(TLE_Comparison_Chart::render($safe,'impact'),strlen('data:image/svg+xml;base64,')));
expect('program labels escaped',!str_contains($escape,'<script>')&&str_contains($escape,'&lt;script&gt;'));
echo 'Checks: '.$checks.'; failures: '.$fails.PHP_EOL;
exit($fails?1:0);
