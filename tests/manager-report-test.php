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
require $root.'/includes/class-manager-report-service.php';
$failures=0; $checks=0;
function check($name,$ok) { global $failures,$checks; $checks++; if (!$ok) { $failures++; } echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL; }
function choice_field($id,$labels) {
    $choices=[];
    foreach ($labels as $i=>$label) { $choices[]=['text'=>$label,'value'=>'answer-'.$i,'score'=>count($labels)-$i]; }
    return ['id'=>$id,'type'=>'survey','label'=>'Statement '.$id,'choices'=>$choices];
}
$agreement=['Strongly disagree','Disagree','Neutral','Agree','Strongly agree'];
$field=choice_field(1,$agreement);
$entries=[['1'=>'answer-0'],['1'=>'answer-3'],['1'=>'answer-4'],['1'=>'answer-4'],['1'=>''],['1'=>'unknown'],['1'=>['bad']]];
$r=TLE_Manager_Report_Service::distribution('1','Statement',$field,$entries);
check('five response categories retained',count($r['categories'])===5);
check('valid denominator excludes missing and invalid',$r['answered_count']===4&&$r['missing_count']===1&&$r['invalid_count']===2);
check('positive is agreement labels despite reversed choice scores',$r['positive_count']===3&&$r['positive_percentage']===75.0);
check('raw category percentages correct',array_column($r['categories'],'percentage')===[25.0,0.0,0.0,25.0,50.0]);
check('percentages total 100',abs(array_sum(array_column($r['categories'],'percentage'))-100)<0.000001);
$r=TLE_Manager_Report_Service::distribution('1','Statement',$field,[]);
check('no answers have null percentages',$r['positive_percentage']===null&&$r['categories'][0]['percentage']===null);
$r=TLE_Manager_Report_Service::distribution('1','Statement',$field,[['1'=>'answer-0']]);
check('real zero positive retained',$r['positive_percentage']===0.0);
$r=TLE_Manager_Report_Service::distribution('1','Statement',$field,[['1'=>'Agree']]);
check('display-label entry recognised',$r['positive_percentage']===100.0);
$r=TLE_Manager_Report_Service::distribution('1','Statement',$field,[['1'=>'answer-0']],['positive_choices'=>['Strongly disagree','Disagree']]);
check('explicit positive direction override works',$r['positive_percentage']===100.0);
$satisfaction=choice_field(2,['Extremely dissatisfied','Somewhat disatisfied','Neutral','Somewhat satisfied','Extremely satisfied']);
$r=TLE_Manager_Report_Service::distribution('2','Satisfaction',$satisfaction,[['2'=>'answer-3'],['2'=>'answer-4'],['2'=>'answer-1']]);
check('satisfaction uses satisfied categories',$r['scale']==='satisfaction'&&abs($r['positive_percentage']-200/3)<0.000001);
$four=choice_field(3,['Strongly disagree','Disagree','Agree','Strongly agree']);
$r=TLE_Manager_Report_Service::distribution('3','Employment',$four,[['3'=>'answer-2'],['3'=>'answer-3']]);
check('four-choice employment question supported',count($r['categories'])===4&&$r['positive_percentage']===100.0);
$yes=choice_field(4,['Yes','No']);$yes['type']='radio';
$r=TLE_Manager_Report_Service::distribution('4','Applied learning',$yes,[['4'=>'answer-0'],['4'=>'answer-1']]);
check('yes/no follows label not source order',$r['categories'][0]['label']==='No'&&$r['positive_percentage']===50.0);
check('unrelated choices excluded',TLE_Manager_Report_Service::distribution('9','Role',choice_field(9,['CEO','Other']),[])===null);
$collision=$field;$collision['choices'][0]['value']='same';$collision['choices'][1]['value']='same';
$r=TLE_Manager_Report_Service::distribution('1','Bad choice keys',$collision,[['1'=>'same']]);
check('ambiguous choice token excluded',$r['answered_count']===0&&$r['invalid_count']===1);
$config=['enabled'=>true,'evaluation_stage'=>'manager','fields'=>['program'=>'101','cohort'=>'102','program_group'=>'103','participant_key'=>'104']];
$GLOBALS['forms'][777]=['id'=>777,'title'=>'Future manager form','fields'=>[$field,$satisfaction,$four,$yes,
 ['id'=>101,'type'=>'text','label'=>'Program'],['id'=>102,'type'=>'text','label'=>'Cohort'],['id'=>103,'type'=>'text','label'=>'Group'],['id'=>104,'type'=>'text','label'=>'ID']]];
$GLOBALS['entries'][777]=[
 ['id'=>'1','101'=>'Pride','102'=>'2026','103'=>'I-LEAD','1'=>'answer-4','2'=>'answer-3','3'=>'answer-2','4'=>'answer-0'],
 ['id'=>'2','101'=>'Other','102'=>'2026','103'=>'I-LEAD','1'=>'answer-0'],
 ['id'=>'3','101'=>'Pride','102'=>'2025','103'=>'I-LEAD','1'=>'answer-0'],
 ['id'=>'4','101'=>'','102'=>'','1'=>'answer-4']];
check('save arbitrary manager form',!is_wp_error(TLE_Form_Registry::save(777,$config)));
$r=TLE_Manager_Report_Service::get_report(['program'=>'Pride','cohort'=>'2026']);
check('discover manager forms without fixed ID',!is_wp_error($r)&&$r['groups'][0]['series'][0]['form_id']===777);
check('program and cohort filters isolate entries',$r['submission_count']===1&&$r['groups'][0]['context']['program']==='Pride');
check('all supported types returned',count($r['groups'][0]['series'][0]['questions'])===4);
check('context data not fabricated',$r['groups'][0]['context']['program_group']==='I-LEAD');
$r=TLE_Manager_Report_Service::get_report(['program'=>'Absent','cohort'=>'2026'],[777]);
check('no match distinct from error',!is_wp_error($r)&&$r['status']==='no_matching_responses'&&$r['submission_count']===0);
check('no-match metadata retains questions',count($r['groups'][0]['series'][0]['questions'])===4&&$r['groups'][0]['series'][0]['questions'][0]['positive_percentage']===null);
$GLOBALS['api_error']=401;$r=TLE_Manager_Report_Service::get_report([],[777]);
check('API authentication failure propagated',is_wp_error($r)&&$r->get_error_code()==='tle_api_authentication_failed');unset($GLOBALS['api_error']);
$bad=$config;$bad['fields']['program']='';TLE_Form_Registry::save(777,$bad);
$r=TLE_Manager_Report_Service::get_report(['program'=>'Pride'],[777]);
check('missing context mapping blocks filtered report',is_wp_error($r)&&$r->get_error_code()==='tle_manager_context_unmapped');
TLE_Form_Registry::save(777,$config);
$r=TLE_Manager_Report_Service::get_report([],[[777]]);
check('malformed form IDs rejected',is_wp_error($r));
$r=TLE_Manager_Report_Service::get_report(['program'=>['Pride']],[777]);
check('array filter rejected',is_wp_error($r));
$disabled=$config;$disabled['enabled']=false;TLE_Form_Registry::save(777,$disabled);
check('disabled manager form rejected',is_wp_error(TLE_Manager_Report_Service::get_report([],[777])));
$config['manager_questions']=['1'=>['enabled'=>false],'2'=>['positive_choices'=>['Extremely satisfied']]];
$saved=TLE_Form_Registry::save(777,$config);
check('manager configuration round trip',!is_wp_error($saved)&&TLE_Form_Registry::configuration(777)['manager_questions']['1']['enabled']===false);
$r=TLE_Manager_Report_Service::get_report(['program'=>'Pride','cohort'=>'2026'],[777]);
check('explicit field exclusion respected',count($r['groups'][0]['series'][0]['questions'])===3);
check('explicit satisfaction positive policy respected',$r['groups'][0]['series'][0]['questions'][0]['positive_percentage']===0.0);
$bad=$config;$bad['manager_questions']=['1'=>['enabled'=>'yes']];
check('invalid manager rule rejected',is_wp_error(TLE_Form_Registry::save(777,$bad)));
TLE_Form_Registry::save(777,$config);
$GLOBALS['forms'][778]=$GLOBALS['forms'][777];$GLOBALS['forms'][778]['id']=778;$GLOBALS['entries'][778]=[$GLOBALS['entries'][777][0]];TLE_Form_Registry::save(778,$config);
$r=TLE_Manager_Report_Service::get_report(['program'=>'Pride','cohort'=>'2026'],[777,778]);
check('manager forms keep separate series',count($r['groups'][0]['series'])===2&&$r['submission_count']===2);
$r=TLE_Manager_Report_Service::get_report([],[777]);
check('unfiltered contexts remain separate',count($r['groups'])===4);
$GLOBALS['logged_in']=false;check('manager route uses login permission',!TLE_REST_API::permissions_check());
$GLOBALS['logged_in']=true;check('authenticated permission accepted',TLE_REST_API::permissions_check());
$with_na=$field;$with_na['choices'][]=['text'=>'Not Applicable','value'=>'na','score'=>0];
$r=TLE_Manager_Report_Service::distribution('1','N/A field',$with_na,[['1'=>'na'],['1'=>'answer-4']]);
check('N/A excluded without losing response scale',$r['answered_count']===1&&$r['excluded_count']===1&&$r['positive_percentage']===100.0);
$bad=$config;$bad['manager_questions']=['1'=>['positive_choices'=>['Missing choice']]];TLE_Form_Registry::save(777,$bad);
check('stale positive label cannot silently produce zero',is_wp_error(TLE_Manager_Report_Service::get_report([],[777])));
$likert=$field;$likert['gsurveyLikertRows']=[['text'=>'Row','value'=>'row-key']];
$r=TLE_Manager_Report_Service::distribution('1','Likert',$likert,[['1'=>'row-key:answer-4'],['1'=>'unknown-row:answer-4']]);
check('Likert row and choice tokens decoded with metadata',$r['answered_count']===1&&$r['invalid_count']===1&&$r['positive_percentage']===100.0);
$actual_employment=choice_field(29,['Strongly disagree','Disagree','Neutral','Agree']);
$r=TLE_Manager_Report_Service::distribution('29','Employment',$actual_employment,[['29'=>'answer-2'],['29'=>'answer-3'],['29'=>'answer-3']]);
check('actual four-choice scale retains Neutral',count($r['categories'])===4&&$r['categories'][2]['label']==='Neutral');
check('actual employment positive counts Agree only',abs($r['positive_percentage']-200/3)<0.000001&&$r['positive_definition']===['Agree']);
check('no nonexistent Strongly agree category fabricated',!in_array('Strongly agree',array_column($r['categories'],'label'),true));
echo 'Checks: '.$checks.'; failures: '.$failures.PHP_EOL;
exit($failures?1:0);
