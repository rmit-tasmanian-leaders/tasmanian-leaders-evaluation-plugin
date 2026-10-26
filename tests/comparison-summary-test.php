<?php
/** Standalone regression tests for editable comparison PDF summaries. */
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/includes/class-comparison-summary.php';
$n=0;$fail=0;
function chk($name,$ok){global $n,$fail;$n++;if(!$ok){$fail++;}echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL;}
function selection($name,$year,$positive=26.33){
  $top=[];
  foreach ([
    ['Balanced processing',$positive],
    ['Self-compassion',17.4],
    ['Clarity of purpose',0.0],
    ['Place-attachment',-1.4]
  ] as $value){$top[]=['capability'=>$value[0],'growth'=>$value[1]];}
  return ['program'=>$name,'cohort'=>$year,'label'=>$name.' '.$year,'top_growth'=>[
    'insight'=>$top,'influence'=>$top,'impact'=>$top
  ]];
}
$two=[selection('I-LEAD Women in Industry','2025'),selection('I-LEAD Young Professionals','2024')];
$html=TLE_Comparison_Summary::render_section($two,'insight');
chk('two programs represented',substr_count($html,'class="tle-growth-program"')===2);
chk('one comparison gives six descriptions',substr_count($html,'class="tle-growth-summary-line"')===6);
chk('relative growth percentage used',str_contains($html,'26.33%'));
chk('no percentage-point text',!str_contains($html,'percentage points'));
chk('zero described correctly',str_contains($html,'was unchanged'));
chk('no fourth capability in top three',!str_contains($html,'Place-attachment'));
$three=$two;$three[]=selection('I-LEAD Women in Community','2025');
chk('two comparisons give six descriptions',substr_count(TLE_Comparison_Summary::render_section($three,'impact'),'class="tle-growth-summary-line"')===6);
$four=$three;$four[]=selection('I-LEAD Young Professionals','2025');
chk('three comparisons give four descriptions',substr_count(TLE_Comparison_Summary::render_section($four,'influence'),'class="tle-growth-summary-line"')===4);
$two[0]['top_growth']['insight'][0]['growth']=-25.00;
chk('negative growth described as decline',str_contains(TLE_Comparison_Summary::render_section($two,'insight'),'declined by 25.00%'));
$pk=TLE_Comparison_Summary::field_key('I-LEAD Women in Industry|2025');
$ck=TLE_Comparison_Summary::field_key('Balanced processing');
$over=['insight'=>[$pk=>[$ck=>'My custom <script>alert(1)</script> description.']]];
$custom=TLE_Comparison_Summary::render_section($two,'insight',$over);
chk('custom narrative used',str_contains($custom,'My custom'));
chk('HTML is escaped',!str_contains($custom,'<script>')&&str_contains($custom,'&lt;script&gt;'));
chk('other program retains automatic narrative',substr_count($custom,'improved by')>=1);
chk('empty override falls back to automatic text',str_contains(TLE_Comparison_Summary::render_section($two,'insight',['insight'=>[$pk=>[$ck=>'  ']]]),'declined by'));
$special=[selection('Program <danger>','2025'), selection('Safe','2024')];
chk('program labels escaped',str_contains(TLE_Comparison_Summary::render_section($special,'insight'),'&lt;danger&gt;'));
try{TLE_Comparison_Summary::render_section($two,'unknown');$rejected=false;}catch(InvalidArgumentException $e){$rejected=true;}
chk('invalid sections rejected',$rejected);
try{TLE_Comparison_Summary::render_section([$two[0]],'insight');$rejected=false;}catch(InvalidArgumentException $e){$rejected=true;}
chk('single-program input rejected',$rejected);
try{TLE_Comparison_Summary::render_section(array_fill(0,5,$two[0]),'insight');$rejected=false;}catch(InvalidArgumentException $e){$rejected=true;}
chk('more than four programs rejected',$rejected);
echo 'Checks: '.$n.'; failures: '.$fail.PHP_EOL;exit($fail?1:0);