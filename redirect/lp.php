<?

#only allow numeric ids
$lpip = $_GET['lpip'];
if (!is_numeric($lpip)) die();


#cached redirects stored here:
$myFile = "cached/lp-cached.csv";


# check to see if mysql connection works, if not fail over to cached .CSV stored redirect urls
include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-config.php');

$dbconnect = @mysql_connect($dbhost,$dbuser,$dbpass);
if (!$dbconnect) $usedCachedRedirect = true;

if (!$usedCachedRedirect) $dbselect = @mysql_select_db($dbname);
if (!$dbselect) $usedCachedRedirect = true;

#the mysql server is down, use the txt cached redirect
if ($usedCachedRedirect) {

	$handle = @fopen($myFile, 'r');
	while ($row = @fgetcsv($handle, 100000, ",")) {

		//if a cached key is found for this id, redirect to that url
		if ($row[0] == $lpip) {
			header('location: '. $row[1]);
			die();
		}
	}
	@fclose($handle);

	die("<h2>Error establishing a database connection - please contact the webhost</h2>");
}


include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php');

$mysql['landing_page_id_public'] = mysql_real_escape_string($lpip);
$tracker_sql = "SELECT 202_landing_pages.user_id,
						202_landing_pages.landing_page_id,
						202_landing_pages.landing_page_id_public,
						202_landing_pages.aff_campaign_id,
						202_aff_campaigns.aff_campaign_rotate,
						202_aff_campaigns.aff_campaign_url,
						202_aff_campaigns.aff_campaign_url_2,
						202_aff_campaigns.aff_campaign_url_3,
						202_aff_campaigns.aff_campaign_url_4,
						202_aff_campaigns.aff_campaign_url_5,
						202_aff_campaigns.aff_campaign_payout,
						202_aff_campaigns.aff_campaign_cloaking
				FROM    202_landing_pages, 202_aff_campaigns
				WHERE   202_landing_pages.landing_page_id_public='".$mysql['landing_page_id_public']."'
				AND     202_aff_campaigns.aff_campaign_id = 202_landing_pages.aff_campaign_id";
$tracker_row = memcache_mysql_fetch_assoc($tracker_sql);

if (!$tracker_row) { die(); }



if ( is_writable(dirname(__FILE__) . '/cached' )) {

	#if the file does not exist create it
	if (!file_exists($myFile)) {
		$handle = @fopen($myFile, 'w');
		@fclose($handle);
	}

	# now save this link to the
	$handle = @fopen($myFile, 'r');
	$writeNewIndex = true;
	while (($row = @fgetcsv($handle, 100000, ",")) and ($writeNewIndex == true)) {
		if ($row[0] == $lpip) $writeNewIndex = false;
	}
	@fclose($handle);

	if ($writeNewIndex) {
		//write this index to the txt file
		$newLine = "$lpip, {$tracker_row['aff_campaign_url']} \n";
		$newHandle = @fopen($myFile, 'a+');
		@fwrite($newHandle, $newLine);
		@fclose($newHandle);
	}
}






//grab the GET variables from the LANDING PAGE
$landing_page_site_url_address_parsed = parse_url($_SERVER['HTTP_REFERER']);
parse_str($landing_page_site_url_address_parsed['query'], $_GET);

if ($_GET['t202id']) {
	//grab tracker data if avaliable
	$mysql['tracker_id_public'] = mysql_real_escape_string($_GET['t202id']);

	$tracker_sql2 = "SELECT  text_ad_id,
							ppc_account_id,
							click_cpc,
							click_cloaking
					FROM    202_trackers
					WHERE   tracker_id_public='".$mysql['tracker_id_public']."'";
	$tracker_row2 = memcache_mysql_fetch_assoc($tracker_sql2);
	if ($tracker_row2) {
		$tracker_row = array_merge($tracker_row,$tracker_row2);
	}
}

//INSERT THIS CLICK BELOW, if this click doesn't already exisit

//get mysql variables
$mysql['user_id'] = mysql_real_escape_string($tracker_row['user_id']);
$mysql['aff_campaign_id'] = mysql_real_escape_string($tracker_row['aff_campaign_id']);
$mysql['ppc_account_id'] = mysql_real_escape_string($tracker_row['ppc_account_id']);
$mysql['click_cpc'] = mysql_real_escape_string($tracker_row['click_cpc']);
$mysql['click_payout'] = mysql_real_escape_string($tracker_row['aff_campaign_payout']);
$mysql['click_time'] = time();

$mysql['landing_page_id'] = mysql_real_escape_string($tracker_row['landing_page_id']);
$mysql['text_ad_id'] = mysql_real_escape_string($tracker_row['text_ad_id']);
 /*
if ($_GET['OVRAW']) { //if this is a Y! keyword
	$keyword = mysql_real_escape_string($_GET['OVRAW']);
} else {
	$keyword = mysql_real_escape_string($_GET['t202kw']);
}
$keyword = str_replace('%20',' ',$keyword);
$keyword_id = INDEXES::get_keyword_id($keyword);
$mysql['keyword_id'] = mysql_real_escape_string($keyword_id);

$ip_id = INDEXES::get_ip_id($_SERVER['REMOTE_ADDR']);
$mysql['ip_id'] = mysql_real_escape_string($ip_id);

$platform_id = INDEXES::get_platform_id();
$mysql['platform_id'] = mysql_real_escape_string($platform_id);

$browser_id = INDEXES::get_browser_id();
$mysql['browser_id'] = mysql_real_escape_string($browser_id);

$mysql['click_in'] = 0;
$mysql['click_out'] = 1;
*/
/*
//this script is going to detect if this click was already recorded by the javascript
$mysql['test_time'] = time() - 60*10; //10 minutes

$click_sql = "SELECT    click_id, click_cloaking, click_cloaking_site_url_id, click_redirect_site_url_id
			  FROM      ((clicks LEFT JOIN clicks_record USING (click_id)) LEFT JOIN clicks_advance USING (click_id)) LEFT JOIN clicks_site USING (click_id)
			  WHERE     clicks.click_time >= ".$mysql['test_time'] ."
			  AND       clicks.user_id = ".$mysql['user_id'] ."
			  AND       clicks.aff_campaign_id = '".$mysql['aff_campaign_id']."'
			  AND       clicks.ppc_account_id = '".$mysql['ppc_account_id']."'
			  AND       clicks.click_cpc = '".$mysql['click_cpc']."'
			  AND       clicks.click_payout = '".$mysql['click_payout']."'
			  AND       clicks_advance.landing_page_id = '".$mysql['landing_page_id']."'
			  AND       clicks_advance.text_ad_id = '".$mysql['text_ad_id']."'
			  AND       clicks_advance.keyword_id = '".$mysql['keyword_id']."'
			  AND       clicks_advance.ip_id = '".$mysql['ip_id']."'
			  AND       clicks_advance.platform_id = '".$mysql['platform_id']."'
			  AND       clicks_advance.browser_id = '".$mysql['browser_id']."'
			  AND       clicks_record.click_in = 1
			  AND       clicks_record.click_out = 0
			  ORDER BY  click_id DESC";
$click_result = mysql_query($click_sql) or record_mysql_error($click_sql);

//now update the old click if the click was detected, and die.
if (mysql_num_rows($click_result) > 0) {
	$click_row = mysql_fetch_assoc($click_result);
	$mysql['click_id'] = mysql_real_escape_string($click_row['click_id']);

	$click_sql = "UPDATE    clicks_record
				  SET       click_out='".$mysql['click_out']."'
				  WHERE     click_id='".$mysql['click_id']."'";
	$click_result = mysql_query($click_sql) or record_mysql_error($click_sql);

	//see if cloaking was turned on
	if ($click_row['click_cloaking'] == 1) {
		$cloaking_on = true;
		$mysql['site_url_id'] = mysql_real_escape_string($click_row['click_cloaking_site_url_id']);
		$site_url_sql = "SELECT site_url_address FROM site_urls WHERE site_url_id='".$mysql['site_url_id']."'";
		$site_url_result = mysql_query($site_url_sql) or record_mysql_error($site_url_sql);
		$site_url_row = mysql_fetch_assoc($site_url_result);
		$cloaking_site_url = $site_url_row['site_url_address'];
	} else {
		$cloaking_on = false;
		$mysql['site_url_id'] = mysql_real_escape_string($click_row['click_redirect_site_url_id']);
		$site_url_sql = "SELECT site_url_address FROM site_urls WHERE site_url_id='".$mysql['site_url_id']."'";
		$site_url_result = mysql_query($site_url_sql) or record_mysql_error($site_url_sql);
		$site_url_row = mysql_fetch_assoc($site_url_result);
		$redirect_site_url = $site_url_row['site_url_address'];
	}

	//now we've updated, lets redirect
	if ($cloaking_on == true) {
		//if cloaked, redirect them to the cloaked site.
		header ('location: '.$cloaking_site_url);
	} else {
		header ('location: '.$redirect_site_url);
	}

	//die this script, we've updated the old click
	die();
}

 */
 /*
//ok we have the main data, now insert this row
$click_sql = "INSERT INTO   clicks
			  SET           user_id=".$mysql['user_id'].",
							aff_campaign_id = '".$mysql['aff_campaign_id']."',
							ppc_account_id = '".$mysql['ppc_account_id']."',
							click_cpc = '".$mysql['click_cpc']."',
							click_payout = '".$mysql['click_payout']."',
							click_time = '".$mysql['click_time']."'";
$click_result = mysql_query($click_sql) or record_mysql_error($click_sql);

//now gather the info for the advance click insert
$click_id = mysql_insert_id();
$mysql['click_id'] = mysql_real_escape_string($click_id);


//now we have the click's advance data, now insert this row
$click_sql = "INSERT INTO   clicks_advance
			  SET           click_id='".$mysql['click_id']."',
							landing_page_id='".$mysql['landing_page_id']."',
							text_ad_id='".$mysql['text_ad_id']."',
							keyword_id='".$mysql['keyword_id']."',
							ip_id='".$mysql['ip_id']."',
							platform_id='".$mysql['platform_id']."',
							browser_id='".$mysql['browser_id']."'";
$click_result = mysql_query($click_sql) or record_mysql_error($click_sql);
 */

//now gather variables for the clicks record db
//lets determine if cloaking is on
if (($tracker_row['click_cloaking'] == 1) or //if tracker has overrided cloaking on
	(($tracker_row['click_cloaking'] == -1) and ($tracker_row['aff_campaign_cloaking'] == 1)) or
	((!isset($tracker_row['click_cloaking'])) and ($tracker_row['aff_campaign_cloaking'] == 1)) //if no tracker but but by default campaign has cloaking on
) {
	$cloaking_on = true;
	$mysql['click_cloaking'] = 1;
	//if cloaking is on, add in a click_id_public, because we will be forwarding them to a cloaked /cl/xxxx link
	$click_id_public = rand(1,9) . $click_id . rand(1,9);
	$mysql['click_id_public'] = mysql_real_escape_string($click_id_public);
} else {
	$mysql['click_cloaking'] = 0;
}

/*
//ok we have our click recorded table, now lets insert theses
$click_sql = "INSERT INTO   clicks_record
			  SET           click_id='".$mysql['click_id']."',
							click_id_public='".$mysql['click_id_public']."',
							click_cloaking='".$mysql['click_cloaking']."',
							click_in='".$mysql['click_in']."',
							click_out='".$mysql['click_out']."'";
$click_result = mysql_query($click_sql) or record_mysql_error($click_sql);

//now lets get variables for clicks site
$click_landing_site_url_id = INDEXES::get_site_url_id($_SERVER['HTTP_REFERER']);
$mysql['click_landing_site_url_id'] = mysql_real_escape_string($click_landing_site_url_id);

$outbound_site_url = 'http://'.$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$click_outbound_site_url_id = INDEXES::get_site_url_id($outbound_site_url);
$mysql['click_outbound_site_url_id'] = mysql_real_escape_string($click_outbound_site_url_id);
 */
if ($cloaking_on == true) {

	$cloaking_site_url = 'http://'.$_SERVER['SERVER_NAME'] . '/redirect/lpc.php?lpip=' . $tracker_row['landing_page_id_public'];
	$click_cloaking_site_url_id = INDEXES::get_site_url_id($cloaking_site_url);
	$mysql['click_cloaking_site_url_id'] = mysql_real_escape_string($click_cloaking_site_url_id);

}

$url = rotateTrackerUrl($tracker_row);
$redirect_site_url = $url . $click_id;
$click_redirect_site_url_id = INDEXES::get_site_url_id($redirect_site_url);
$mysql['click_redirect_site_url_id'] = mysql_real_escape_string($click_redirect_site_url_id);
 /*
//insert this
$click_sql = "INSERT INTO   clicks_site
			  SET           click_id='".$mysql['click_id']."',
							click_landing_site_url_id='".$mysql['click_landing_site_url_id']."',
							click_outbound_site_url_id='".$mysql['click_outbound_site_url_id']."',
							click_cloaking_site_url_id='".$mysql['click_cloaking_site_url_id']."',
							click_redirect_site_url_id='".$mysql['click_redirect_site_url_id']."'";
$click_result = mysql_query($click_sql) or record_mysql_error($click_sql);

//before we finish filter this click
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_id = $tracker_row['user_id'];

FILTER::startFilter($click_id,$ip_id,$ip_address,$user_id);
 */
//now we've recorded, now lets redirect them
if ($cloaking_on == true) {
	//if cloaked, redirect them to the cloaked site.
	header('location: '.$cloaking_site_url);
} else {
	header('location: '.$redirect_site_url);
}