<?php

/* --------------------------------------------------
 * Common Helper functions 
 * Don't put any query related method here
 * --------------------------------------------------
 */
global $preCss, $is_backtrace;
global $prof_timing, $prof_names;

$preCss = "style='border:1px solid #CCC;background-color:#EEE;padding:5px;margin-top:50px;'";
$is_backtrace = true;
/*
 * Formated print_r
 * Usage p($data,$data2,$data3);
 */

function p() {
    global $preCss;
    global $is_backtrace;
    $backtrace_txt = '';

    $op = '<div ' . $preCss . '>';
    if ($is_backtrace) {
        $bt = debug_backtrace();
	
        foreach ($bt as $key => $btrace) {
            if (!in_array($btrace["function"], ["p"]) && strpos($btrace["file"],"Functions.php")==false) {
                $debugArray = $btrace;
                break;
            }
        }
        $backtrace_txt = '<div style="background-color:#DDD;padding:3px;maring-top:-2px;"><b>' . $debugArray['file'] . '</b>: <b>' . $debugArray['line'] . '</b></div><br/>';
    }
    $op .= $backtrace_txt;
    $args = func_get_args();
    foreach ($args as $k => $arg) {
        $op .= "<pre style='border: 1px dotted;padding:10px;background-color:#FFF;'>";
        if (is_array($arg) || is_object($arg)) {
            $op .= print_r($arg, true);
        } else {
            $op .= $arg;
        }
        $op .= "</pre><br />";
    }
    $op .= '</div><br />';
    echo $op;
}

function off_backtrace() {
    global $is_backtrace;
    $is_backtrace = false;
}

// Only print
function pr() {
    $args = func_get_args();
    call_user_func_array('p', $args);
}

// Print + exit
function pe() {
    $args = func_get_args();
    call_user_func_array('p', $args);
    exit();
}

/* @Desc Use Only for debug : echo + exit  e.g. ee("hello",1,array("My Value","Test Value"));
 * @params : string and array both allowed
 */
function ee() {
    $args = func_get_args();
    foreach ($args as $k => $arg) {
        if (is_array($arg) || is_object($arg)) {
            echo '<pre>';
            print_r($arg);
            echo '</pre>';
        } else {
            echo $arg;
        }
    }
    exit;
}

/* @Desc : Print anying no of times : e.g printNtimes('<br/>',10); */
function printNtimes($printValue, $no = 1) {
    for ($i = 1; $i <= $no; $i++) {
        echo $printValue;
    }
}

/*
 * @Var Dumper multiple e.g. vd($arr1,$arr2,$var1,$var2) and exit
 */
function vd() {
    global $preCss;
    global $is_backtrace;
    $backtrace_txt = '';

    echo '<div ' . $preCss . '>';
    if ($is_backtrace) {
        $bt = debug_backtrace();
        $debugArray = $bt[0];
        if (!isset($debugArray['file'])) {
            $debugArray = $bt[2];
        }
        $backtrace_txt = '<b>' . $debugArray['file'] . '</b>: <b>' . $debugArray['line'] . '</b><br/><br/>';
    }
    echo $backtrace_txt;
    $args = func_get_args();
    foreach ($args as $k => $arg) {
        if ($arg == 'exit') {
            exit;
        }
        echo "<pre style='border: 1px solid #ccc;padding:10px;'>";
        var_dump($arg);
        echo "</pre><br />";
    }
    echo '</div><br />';
}

/*
 * @Desc : String Function :: Camelcase string
 *  str_titlecase("test word") => Test Word
 *  str_titlecase("test word(someting)",["("]) => Test Word(Someting) 
 */

function str_titlecase($string, $cases = []) {

    if (isset($cases) && !empty($cases)) {

        foreach ($cases as $case) {

            $string_arr = explode($case, $string);
            if (isset($string_arr) && !empty($string_arr)) {

                $string_arr = array_map("ucfirst", array_map("strtolower", $string_arr));
                $string = implode($case, $string_arr);
            }
        }
        return ucwords($string);
    }
    return ucwords(strtolower($string));
}

/*
 * @Desc : Array Function : Change array values to title case 
 * @param : $array = [1=>"hello world",4=>'thank you'];
 * @return : $array = [1=>"Hello World",4=>'Thank You'];
 */

function array_values_titlecase($array) {
    return array_map("ucwords", array_map('strtolower', $array));
}

function getP($exit = false) {
    if (isset($_GET) && !empty($_GET)) {
        pr($_GET);
        if ($exit)
            exit;
    }
}

function postP($exit = false) {
    if (isset($_POST) && !empty($_POST)) {
        pr($_POST);
        if ($exit)
            exit;
    }
}

/**
 * @Desc : Array Function - check string start with given needle or not 
 * @Return : boolean
 * $str = '|apples}';
 * 	echo str_endsWith($str, '}'); //Returns true
 */
function str_startsWith($haystack, $needle) {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

/**
 * @Desc : Array Function - check string end with given needle or not 
 * $str = '|apples}';
 * 	echo str_endsWith($str, '}'); //Returns true
 * @Return : boolean
 */
function str_endsWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}

function getYesNo($value) {
    return $value ? 'Yes' : 'No';
}

function xmlToArray($xml) {
    $ob = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    $json = json_encode($ob);
    $configData = json_decode($json, true);
    return $configData;
}


function pXML($xml){	
	header ("Cache-Control: no-cache, must-revalidate");
	header ("Pragma: no-cache");
	header ("Content-type: text/xml");
	header ("Content-Description: PHP Generated Data" );
	exit($xml);
}

/*PHP Error Reporting On*/
function phpError(){
	ini_set('display_errors','On');
	error_reporting(E_All);
}

// Debug PHP Error from your end : add ?phpEr=true
function debugPhpError(){
	if(isset($_GET["phpEr"]) && $_GET["phpEr"] ===true){ phpError();}
}
debugPhpError();

/*Json Pretty Print
 * $arr : Pass Array or Json
 */
function jsonP($arr,$exit=true){
	global $preCss;
	global $is_backtrace;
	
	$op = "<div ".$preCss.">";
	 if ($is_backtrace) {
        $bt = debug_backtrace();
        foreach ($bt as $key => $btrace) {
            if (!in_array($btrace["function"], ["p"])) {
                $debugArray = $btrace;
                break;
            }
        }		
		$op .= '<div style="background-color:#DDD;padding:3px;margin-top:1px;"><b>' . $debugArray['file'] . '</b>: <b>' . $debugArray['line'] . '</b></div>';
    }

	$op .= "<pre style='border: 1px dotted;padding:10px;background-color:#FFF;'>";	
	if(is_array($arr)){
		$op .= json_encode($arr,JSON_PRETTY_PRINT);
	}else{
		$op .= json_encode(json_decode($arr),JSON_PRETTY_PRINT);
	}
	$op .= "</pre><br/>";
	
	$op .= "</div>";
	echo $op;	
	if($exit)exit();
}


/*@Desc: For Debuging Print. It will show only if you pass debug=1 in query string */
function dpr(){
	if(isset($_GET["debug"]) && $_GET["debug"]==1){
	   $args = func_get_args();
	   call_user_func_array('p', $args);
	}
}

/*@Desc: For Debuging Print. It will show only if you pass debug=1 in query string */
function dpe(){
	if(isset($_GET["debug"]) && $_GET["debug"]==1){
	   $args = func_get_args();
	   call_user_func_array('p', $args);
	   exit();
	}
}

/*
Yii:: Query and Data Print 
@$qry: object of query 
*/
function qpr($qry,$data=''){
	$data ?	pr($qry->createCommand()->rawSql,$data):pr($qry->createCommand()->rawSql);
}
/* Yii:: Query and Data Print + exit
 *  @$qry: object of query 
 */
function qpe($qry,$data=''){
	$data ?	pe($qry->createCommand()->rawSql,$data):pe($qry->createCommand()->rawSql);
}

function code($code){
    return "<pre><xmp>$code</xmp></pre>";
    return "<pre>". str_replace(">","&gt;",str_replace("<","&lt;",$code))."</pre>";
}

/* Desc : TenGigabitEthernet0/0/8 return 0/0/8 */
function string_after_number($str){
    $ln =strlen($str);
    for($i=0;$i<$ln;$i++){
        if(is_numeric($str[$i]))break;      
    }
    return substr($str,$i,$ln);
}

function jsonPretty($data){
	$data = !is_array($data)?json_decode($data):$data;
	return "<pre style='border:0;background-color:transparent;'>".json_encode($data,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."</pre>";
}
