<?php

    ob_start();

$version = '1.3.2';

DEFINE(TRACKING202_API_URL, 'https://api.tracking202.com');
DEFINE(TRACKING202_RSS_URL, 'http://rss.tracking202.com');

define('APP_DIR', dirname(__FILE__));
define('VENDOR_DIR', APP_DIR.'/vendors');
define('ROOT_DIR', dirname(APP_DIR));

@ini_set('auto_detect_line_endings', TRUE);
@ini_set('register_globals', 0);
@ini_set('display_errors', 'On');
@ini_set('error_reporting', 6135);
@ini_set('safe_mode', 'Off');

//set navigation variable
$navigation = $_SERVER['REQUEST_URI'];
$navigation = explode('/', $navigation);

foreach( $navigation as $key => $row ) {
	$split_chars = preg_split('/\?{1}/', $navigation[$key],-1,PREG_SPLIT_OFFSET_CAPTURE);
	$navigation[$key] = $split_chars[0][0];
}

$_SERVER['HTTP_X_FORWARDED_FOR'] = $_SERVER['REMOTE_ADDR'];

include_once(APP_DIR.'/functions.php');
$info = host_info($_SERVER['HTTP_HOST']); //$_SERVER["HTTP_HOST_"]);
global $reservedSubDomain;
if (!isset($info['subdomain']) || $info['subdomain'] == $reservedSubDomain) {
	if (!empty($_SESSION['subdomain'])) {
		header("Location: http://{$_SESSION['subdomain']}.{$info['domain']}");
	} else {
	    header("Location: http://auth.{$info['domain']}");	
	}
    exit;
} else {
    $subdomain = $info['subdomain'];
}


function host_info($host)
{
    $parts = explode('.', $host);

    $tld = array_pop($parts);
    $domain = array_pop($parts);

    if (count($parts) > 0) {
        $subdomain = array_shift($parts);
    }

    return array(
        'domain'=>sprintf("%s.%s", $domain, $tld),
        'subdomain'=>$subdomain
    );
}

//include mysql settings
include_once(ROOT_DIR.'/xtracks-config.php');
include_once(APP_DIR.'/db.php');


include_once(APP_DIR.'/classes/Auth.php');
//include_once(APP_DIR.'/functions-export202.php');
include_once(APP_DIR.'/functions-tracking202.php');
//include_once(APP_DIR.'/user.php');
include_once(VENDOR_DIR.'/functions-rss.php');
include_once(APP_DIR.'/l10n.php');
include_once(APP_DIR.'/formatting.php');
include_once(VENDOR_DIR.'/class-curl.php');
include_once(VENDOR_DIR.'/class-xmltoarray.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-charts/charts.php');
include_once(APP_DIR.'/classes/Config.php');

global $dbhost, $dbuser, $dbpass, $dbname;

if (strpos($_SERVER['SCRIPT_NAME'], 'install') !== false) {
	db::init($dbhost, $dbuser, $dbpass, null);
} else {
	include_once(APP_DIR.'/sessions.php');
	global $mode;
	if ($mode != 'single') {
		$info = 
		$subdomain = Auth::getClientSite();
		
		if ($subdomain == 'auth') {
		    $dbname = 'prosper_master';
		    db::init($dbhost, $dbuser, $dbpass, $dbname);
		} else {
			
            if (isset($_SERVER['subdomain'])) {
            	$subdomain = $_SERVER['subdomain'];
            }
            $name = $subdomain == $reservedSubDomain ? 'prosper_master' : "prosper_$subdomain";
		    db::init($dbhost, $dbuser, $dbpass, $name);
		}
	} else {
		if ($dbname == '<insert db host here>') {
			die('Please configure the system config file');
		}
	}
	
    
    $dbname = "`$dbname`";
    $config = new Config(0);
}







//try to connect to memcache server
if ( ini_get('memcache.default_port') ) {
	$memcacheInstalled = true;
	$memcache = new Memcache;
	if ( @$memcache->connect($mchost, 11211) )  	$memcacheWorking = true;
	else 												$memcacheWorking = false;
}

//stop the sessions if this is a redirect or a javascript placement, we were recording sessions on every hit when we don't need it on
if ($navigation[1] == 'tracking202') {
	switch ($navigation[2]) {
		case "redirect":
		case "static":
			$stopSessions = true;
			break;
	}
}

//if the mysql tables are all installed now
if ((!($navigation[1]) or ($navigation[1] != '202-config')) and strpos($_SERVER['SCRIPT_NAME'], 'install') === false) {

	//we can initalize the session managers
	if (!$stopSessions) {
		$sess = new SessionManager();
		session_start();
	}

	// Run the cronjob checker.
	//include_once($_SERVER['DOCUMENT_ROOT'] . '/202-cronjobs/index.php');
}

// Set token to prevent CSRF attacks
if (!isset($_SESSION['token']) && !$stopSessions) {
    $_SESSION['token'] = md5(uniqid(rand(), TRUE));
}
