<?php
/** Standalone contract tests: php tests/backend-test.php (no credentials or live requests). */
define('ABSPATH', __DIR__);
define('TLE_GF_API_BASE_URL', 'https://fixture.invalid/wp-json/gf/v2/');
define('TLE_GF_CONSUMER_KEY', 'fixture'); define('TLE_GF_CONSUMER_SECRET', 'fixture');
class WP_Error {
    private $code; private $message; private $data;
    function __construct($code, $message, $data = []) { $this->code=$code; $this->message=$message; $this->data=$data; }
    function get_error_code() { return $this->code; }
    function get_error_message() { return $this->message; }
    function get_error_data() { return $this->data; }
}
class WP_REST_Server { const READABLE = 'GET'; }
function is_wp_error($v) { return $v instanceof WP_Error; }
function absint($v) { return abs((int)$v); }
function sanitize_text_field($v) { return trim(strip_tags((string)$v)); }
function sanitize_textarea_field($v) { return sanitize_text_field($v); }
function plugin_dir_path($v) { return dirname($v).'/'; }
function trailingslashit($v) { return rtrim($v,'/').'/'; }
function wp_json_encode($v, $flags = 0) { return json_encode($v,$flags); }
function add_action($a,$b) {}
function get_option($k,$default = false) { return $GLOBALS['options'][$k] ?? $default; }
function update_option($k,$v,$autoload = null) { $GLOBALS['options'][$k]=$v; }
function is_user_logged_in() { return !empty($GLOBALS['logged_in']); }
function current_user_can($cap) { return !empty($GLOBALS['admin']); }
function rest_ensure_response($v) { return $v; }
function wp_remote_retrieve_response_code($r) { return $r['status']; }
function wp_remote_retrieve_body($r) { return $r['body']; }
function wp_remote_get($url,$args) {
    if (!empty($GLOBALS['api_error'])) { return ['status' => $GLOBALS['api_error'], 'body'=>'{}']; }
    $path=parse_url($url,PHP_URL_PATH); parse_str(parse_url($url,PHP_URL_QUERY) ?? '',$query);
    if (preg_match('~/forms/(\d+)/entries$~',$path,$m)) {
        $entries=$GLOBALS['entries'][(int)$m[1]] ?? [];
        $search=json_decode($query['search'] ?? '{}',true);
        foreach ($search['field_filters'] ?? [] as $filter) {
            if (!is_array($filter)) { continue; }
            $entries=array_values(array_filter($entries,static function($e) use($filter) { return ($e[$filter['key']] ?? '') === $filter['value']; }));
        }
        $offset=(int)($query['paging']['offset'] ?? 0); $size=(int)($query['paging']['page_size'] ?? 100);
        $body=['total_count'=>count($entries),'entries'=>array_slice($entries,$offset,$size)];
    } elseif (preg_match('~/forms/(\d+)$~',$path,$m)) {
        $body=$GLOBALS['forms'][(int)$m[1]] ?? null;
        if (!$body) { return ['status'=>404,'body'=>'{}']; }
    } else { $body=array_values($GLOBALS['forms']); }
    return ['status'=>200,'body'=>json_encode($body)];
}
$root=dirname(__DIR__);
foreach (['class-form-registry','class-gravity-forms-provider','class-evaluation-data-service','class-reporting-service','class-report-service','class-rest-api'] as $file) { require $root.'/includes/'.$file.'.php'; }
$failures=0; $checks=0;
function check($name,$ok) { global $failures,$checks; $checks++; if (!$ok) { $failures++; } echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL; }
$index=TLE_Form_Registry::item_index();
check('document contains 48 items',count($index)===48);
check('document capability counts',array_map('count',TLE_Form_Registry::catalog())===['insight'=>7,'influence'=>6,'impact'=>4]);
$fields=[]; $base_entry=['id'=>'1','date_created'=>'2026-10-08 00:00:00','100'=>'A','101'=>'Pride','102'=>'2026','103'=>'Leadership'];
$i=0;
foreach ($index as $item) {
    $i++; $choices=[];
    $is_percent=strpos($item['text'],'What percentage')===0;
    $is_na=strpos($item['text'],'I can tell how')===0;
    $max=$is_percent?5:($is_na?8:7);
    for($v=1;$v<=$max;$v++) { $choices[]=['text'=>$is_na&&$v===1?'Not Applicable':'Choice '.$v,'value'=>'v'.$v,'score'=>(string)$v]; }
    $fields[]=['id'=>$i,'label'=>$item['text'],'type'=>'survey','choices'=>$choices];
    $base_entry[(string)$i]='v'.($is_percent?3:($is_na?5:4));
}
foreach (['100'=>'Participant ID','101'=>'Program','102'=>'Year','103'=>'Program Group'] as $id=>$label) { $fields[]=['id'=>$id,'type'=>'text','label'=>$label]; }
foreach ([7001,7002,7003] as $id) { $GLOBALS['forms'][$id]=['id'=>$id,'title'=>'Future form '.$id,'fields'=>$fields]; $GLOBALS['entries'][$id]=[$base_entry]; }
$config=['enabled'=>true,'evaluation_stage'=>'pre_program','fields'=>['participant_key'=>'100','program'=>'101','cohort'=>'102','program_group'=>'103'],'question_overrides'=>[]];
check('save arbitrary new form profile',!is_wp_error(TLE_Form_Registry::save(7001,$config)));
$config2=$config; $config2['evaluation_stage']='completion'; TLE_Form_Registry::save(7002,$config2);
check('discover forms without fixed list',count(TLE_Form_Registry::discover())===3);
$profile=TLE_Form_Registry::profile(7001);
check('exact matching resolves all document items',count($profile['questions'])===48 && !$profile['missing_items']);
$records=TLE_Evaluation_Data_Service::get_all_evaluations(7001,[],1);
check('normalise arbitrary form',!is_wp_error($records)&&count($records)===48);
check('preserve form and participant context',$records[0]['form_id']===7001&&$records[0]['participant_key']==='A'&&$records[0]['program_group']==='Leadership');
$report=TLE_Report_Service::get_report(['program'=>'Pride','cohort'=>'2026'],[7001]);
check('report retrieval succeeds',!is_wp_error($report));
if (!is_wp_error($report)) {
 foreach ($report['capability_averages'] as $section=>$caps) { foreach ($caps as $cap=>$stages) { check($cap.' midpoint equals 4',($stages['pre_program']??null)===4.0); } }
 check('raw records preserved',$report['records']===$records);
 check('raw question keys isolated by form',isset($report['question_averages']['form-7001:1']));
 check('counts correct',$report['participant_count']===1&&$report['submission_count']===1&&$report['record_count']===48);
}
$rule=['raw_min'=>1,'raw_max'=>7,'target_min'=>1,'target_max'=>7,'reverse'=>true,'allowed_scores'=>range(1,7)];
check('reverse lower endpoint',TLE_Reporting_Service::prepare_capability_score('x',1,$rule)===7.0);
check('reverse upper endpoint',TLE_Reporting_Service::prepare_capability_score('x',7,$rule)===1.0);
check('invalid score rejected',TLE_Reporting_Service::prepare_capability_score('x',9,$rule)===null);
check('missing score excluded',TLE_Reporting_Service::prepare_capability_score('x',null,$rule)===null);
$na_rule=['raw_min'=>2,'raw_max'=>8,'reverse'=>false,'excluded_scores'=>[1],'allowed_scores'=>range(2,8)];
check('N/A excluded',TLE_Reporting_Service::prepare_capability_score('x',1,$na_rule)===null);
check('eight category valid midpoint',TLE_Reporting_Service::prepare_capability_score('x',5,$na_rule)===4.0);
check('five category rescale',TLE_Reporting_Service::prepare_capability_score('x',2,['raw_min'=>1,'raw_max'=>5])===2.5);
$multi=TLE_Report_Service::get_report(['program'=>'Pride','cohort'=>'2026'],[7001,7002]);
check('future completion form scores',!is_wp_error($multi)&&$multi['capability_averages']['insight']['Self-awareness']['completion']===4.0);
check('cross-form submission IDs distinct',!is_wp_error($multi)&&$multi['submission_count']===2&&$multi['participant_count']===1);
check('new unconfigured form needs setup',is_wp_error(TLE_Report_Service::get_report([],[7003])));
$adapter=tle_get_report_data('Pride','2026',[7001]);
check('adapter preserves PDF keys',!is_wp_error($adapter)&&count($adapter['insight'])===7&&$adapter['impact'][0]['completion']===null);
$other=$base_entry; $other['id']='2'; $other['100']='B'; $other['103']='Other';
$GLOBALS['entries'][7001][]=$other;
$groups=TLE_Report_Service::get_report(['program'=>'Pride','cohort'=>'2026'],[7001]);
check('program groups kept separate',!is_wp_error($groups)&&count($groups['groups'])===2&&$groups['capability_averages']['insight']['Self-awareness']===[]);
check('PDF refuses merged program groups',is_wp_error(tle_get_report_data('Pride','2026',[7001])));
$selected=tle_get_report_data('Pride','2026',[7001],['program_group'=>'Leadership']);
check('PDF filtered program group works',!is_wp_error($selected)&&$selected['participant_count']===1);
$individual=TLE_Report_Service::get_report(['participant_key'=>'B'],[7001]);
check('participant filter works',!is_wp_error($individual)&&$individual['participant_count']===1&&$individual['record_count']===48);
$no_context=$config; $no_context['fields']['program']=''; TLE_Form_Registry::save(7003,$no_context);
$missing=TLE_Report_Service::get_report(['program'=>'Pride'],[7003]);
check('missing context explicit error',is_wp_error($missing)&&$missing->get_error_code()==='tle_missing_context_mapping');
$GLOBALS['api_error']=401;
$err=TLE_Gravity_Forms_Provider::get_forms();
check('authentication error retained',is_wp_error($err)&&$err->get_error_code()==='tle_api_authentication_failed');
unset($GLOBALS['api_error']);
check('unauthenticated route denied',!TLE_REST_API::permissions_check());
$GLOBALS['logged_in']=true; check('authenticated route allowed',TLE_REST_API::permissions_check());
check('non-admin cannot configure forms',!TLE_REST_API::setup_permissions_check());
$GLOBALS['admin']=true; check('admin can configure forms',TLE_REST_API::setup_permissions_check());
$bad=$config; $bad['evaluation_stage']='bad stage'; check('invalid stage rejected',is_wp_error(TLE_Form_Registry::save(7001,$bad)));
$bad=$config; $bad['question_overrides']=['1'=>['item_id'=>'unknown']]; check('unknown document item rejected',is_wp_error(TLE_Form_Registry::save(7001,$bad)));
// Multi-page retrieval, duplicate matches and row identity tests.
$original_entries=$GLOBALS['entries'][7001];
$GLOBALS['entries'][7001]=[];
for ($j=1;$j<=7;$j++) { $e=$base_entry; $e['id']=(string)$j; $GLOBALS['entries'][7001][]=$e; }
$pages=TLE_Gravity_Forms_Provider::get_all_entries(7001,[],2);
check('multi-page complete retrieval',!is_wp_error($pages)&&count($pages)===7&&count(array_unique(array_column($pages,'id')))===7);
$GLOBALS['entries'][7001]=$original_entries;
TLE_Form_Registry::save(7003,$config);
$same=TLE_Report_Service::get_report(['program'=>'Pride','cohort'=>'2026','program_group'=>'Leadership'],[7001,7003]);
check('two forms at same stage retain separate series',!is_wp_error($same)&&count($same['groups'][0]['series'])===2&&$same['capability_averages']['insight']['Self-awareness']===[]);
check('legacy PDF refuses same-stage ambiguity',is_wp_error(tle_get_report_data('Pride','2026',[7001,7003],['program_group'=>'Leadership'])));
$form=$GLOBALS['forms'][7003]; $form['id']=8001;
$form['fields'][]=$fields[0]; $form['fields'][count($form['fields'])-1]['id']=90;
$GLOBALS['forms'][8001]=$form; $GLOBALS['entries'][8001]=[$base_entry];
TLE_Form_Registry::save(8001,$config);
$p=TLE_Form_Registry::profile(8001);
check('duplicate document matches flagged',in_array('1',$p['ambiguous_fields'],true)&&in_array('90',$p['ambiguous_fields'],true));
check('ambiguous profile blocks reporting',is_wp_error(TLE_Report_Service::get_report([],[8001])));
$fixed=$config; $fixed['question_overrides']=['90'=>['item_id'=>'']]; TLE_Form_Registry::save(8001,$fixed);
check('explicit exclusion resolves ambiguity',!TLE_Form_Registry::profile(8001)['ambiguous_fields']);
$missing_form=$GLOBALS['forms'][7003]; $missing_form['id']=8002; array_shift($missing_form['fields']);
$GLOBALS['forms'][8002]=$missing_form; $GLOBALS['entries'][8002]=[$base_entry]; TLE_Form_Registry::save(8002,$config);
check('missing document items reported',count(TLE_Form_Registry::profile(8002)['missing_items'])===1);
$multirow=['fields'=>[['id'=>10,'type'=>'survey','label'=>'Parent', 'gsurveyLikertEnableMultipleRows'=>true,
 'inputs'=>[['id'=>'10.2','label'=>'Second actual question'],['id'=>'10.4','label'=>'Fourth actual question']],
 'gsurveyLikertRows'=>[['text'=>'Wrong positional row'],['text'=>'Another wrong row']]]]];
check('subfields use their own labels',TLE_Form_Registry::fields($multirow)['10.4']['label']==='Fourth actual question');
$unequal=[];
$first=$records[0]; $first['score']=7; $first['scoring_rule']['reverse']=false;
$second=$records[1]; $second['score']=1; $second['scoring_rule']['reverse']=false;
$unequal=[$first,$first,$second];
$equal=TLE_Reporting_Service::generate_report($unequal);
check('equal question weighting preserved',$equal['capability_averages']['insight']['Self-awareness']['pre_program']===4.0);
$second['score']=null;
$missing_report=TLE_Reporting_Service::generate_report([$first,$second]);
check('missing answers excluded rather than zero',$missing_report['capability_averages']['insight']['Self-awareness']['pre_program']===7.0);
// Custom manager items must not masquerade as document items.
$manager_choices=[];
foreach (range(1,5) as $n) { $manager_choices[]=['text'=>'Agreement '.$n,'value'=>'v'.$n,'score'=>$n]; }
$GLOBALS['forms'][9009]=['id'=>9009,'title'=>'Future manager survey','fields'=>[
 ['id'=>1,'type'=>'survey','label'=>'Observed clearer decisions','choices'=>$manager_choices],
 ['id'=>2,'type'=>'text','label'=>'Optional context','choices'=>'','inputs'=>''],
]];
$GLOBALS['entries'][9009]=[['id'=>1,'date_created'=>'2026-10-10','1'=>'v5']];
$custom_config=['catalog_mode'=>'custom','enabled'=>true,'evaluation_stage'=>'manager','fields'=>[], 'custom_items'=>[
 ['id'=>'custom.manager.reasoning','text'=>'Observed clearer decisions','section'=>'insight','capability'=>'Clarity of purpose','reverse'=>false]
]];
check('save configurable custom manager item',!is_wp_error(TLE_Form_Registry::save(9009,$custom_config)));
$mp=TLE_Form_Registry::profile(9009);
check('custom wording resolves independently',!is_wp_error($mp)&&count($mp['questions'])===1&&$mp['questions'][1]['id']==='custom.manager.reasoning');
$mr=TLE_Report_Service::get_report([],[9009]);
check('custom manager item contributes to capability',!is_wp_error($mr)&&($mr['capability_averages']['insight']['Clarity of purpose']['manager']??null)===7.0);
check('unrepresented manager capability unavailable',!is_wp_error($mr)&&empty($mr['capability_averages']['insight']['Self-awareness']));
check('manager diagnostics use custom catalog only',!is_wp_error($mp)&&empty($mp['missing_items']));
check('custom configuration round trip preserves items',count(TLE_Form_Registry::configuration(9009)['custom_items'])===1);
$bad=$custom_config;$bad['custom_items'][0]['capability']='Imaginary capability';
check('unknown custom capability rejected',is_wp_error(TLE_Form_Registry::save(9009,$bad)));
$bad=$custom_config;$bad['custom_items'][]=$bad['custom_items'][0];
check('duplicate custom IDs rejected',is_wp_error(TLE_Form_Registry::save(9009,$bad)));
$reversed=$manager_choices;foreach ($reversed as &$choice) { $choice['score']=6-$choice['score']; }unset($choice);
$GLOBALS['forms'][9010]=['id'=>9010,'title'=>'Already reversed manager item','fields'=>[['id'=>1,'type'=>'survey','label'=>'Observed clearer decisions','choices'=>$reversed]]];
$GLOBALS['entries'][9010]=[['id'=>1,'date_created'=>'2026-10-10','1'=>'v1']];
TLE_Form_Registry::save(9010,$custom_config);
$rr=TLE_Report_Service::get_report([],[9010]);
check('already reversed choice scores not reversed twice',!is_wp_error($rr)&&($rr['capability_averages']['insight']['Clarity of purpose']['manager']??null)===7.0);
echo 'Checks: '.$checks.'; failures: '.$failures.PHP_EOL;
exit($failures?1:0);