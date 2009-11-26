<?php

//our own die, that will display the theme aroudn the error message
function _die($message)
{
	info_top();
	echo $message;
	info_bottom();
	die();
}

//our own function for controling mysqls and monitoring then.
function _mysql_query($sql)
{
	$result = mysql_query($sql) or die(mysql_error() . '<br/><br/>' . $sql);
	return $result;
}

function salt_user_pass($user_pass, $salt='202')
{
    return md5($salt . md5($user_pass . $salt));
}

function forward($url)
{
    $url = strip_tags($url);
    header("Location: $url");
    exit;
}

function client_installed($subdomain)
{
    $s_subdomain = db::escape($subdomain);
    $row = db::getRow("SELECT 1
                       FROM prosper_master.install_jobs
                       WHERE subdomain='{$s_subdomain}'");

    return (bool)$row;
}

function is_installed()
{
	//if a user account already exists, this application is installed
	$user_sql = "SELECT COUNT(*) FROM 202_users";
	$user_result = @mysql_query($user_sql);

	if ($user_result) {
		return true;
	} else {
		return false;
	}
}

/*
function upgrade_needed()
{
	$mysql_version = PROSPER202::mysql_version();
	$php_version = PROSPER202::php_version();
	if ($mysql_version != $php_version) { return true; } else { return false; }
}
*/

function get_base_url()
{
    $php_self = $_SERVER['PHP_SELF'];
    $http_host = $_SERVER['HTTP_HOST'];

    $uri_parts = explode('/', $php_self);
    array_pop($uri_parts);
    $url_dir = join('/', $uri_parts);

    if (substr($url_dir, -1) != '/')
        $url_dir.= '/';

    if ($url_dir[0] == '/')
        $url_dir = substr($url_dir, 1);

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
        $protocol = 'https';
    else
        $protocol = 'http';

    $base_url = sprintf('%s://%s/%s', $protocol, $http_host, $url_dir);

    return $base_url;
}

function mysqlversion()
{
 	$mysql_version_sql = "SELECT VERSION();";
	$mysql_version_result = _mysql_query($mysql_version_sql);
	$mysql_version = mysql_result($mysql_version_result,0,0);
	return $mysql_version;
}

function info_top()
{
    require dirname(__FILE__).'/templates/info_header.php';
}

function info_bottom()
{
    require dirname(__FILE__).'/templates/info_footer.php';
}

function template_top($title='Prosper202 Self Hosted Apps')
{
    global $navigation;
    require dirname(__FILE__).'/templates/display_header.php';
}

function template_bottom()
{
    require dirname(__FILE__).'/templates/display_footer.php';
}

function check_email_address($email)
{
    // First, we check that there's one @ symbol, and that the lengths are right
    if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
        // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
        return false;
    }

    // Split it into sections to make life easier
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
        if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
            return false;
        }
    }
    if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
        $domain_array = explode(".", $email_array[1]);
        if (sizeof($domain_array) < 2) {
            return false; // Not enough parts to domain
        }

        for ($i = 0; $i < sizeof($domain_array); $i++) {
            if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
                return false;
            }
        }
    }
    return true;
}

function print_r_html($data,$return_data=false)
{
	$data = print_r($data,true);
	$data = str_replace( " ","&nbsp;", $data);
	$data = str_replace( "\r\n","<br/>\r\n", $data);
	$data = str_replace( "\r","<br/>\r", $data);
	$data = str_replace( "\n","<br/>\n", $data);

	if (!$return_data)
		echo $data;
	else
		return $data;
}

function html2txt($document)
{
    $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
                   '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
                   '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
                   '@<![\s\S]*?--[ \t\n\r]*>@'        // Strip multi-line comments including CDATA
    );
    $text = preg_replace($search, '', $document);
    return $text;
}

/*
function update_needed ()
{
	global $version;

	 $rss = fetch_rss('http://prosper202.com/apps/currentversion/');
	 if ( isset($rss->items) && 0 != count($rss->items) ) {

		$rss->items = array_slice($rss->items, 0, 1) ;
		foreach ($rss->items as $item ) {

			$latest_version = $item['title'];
			if ($version != $latest_version) {
				return true;
			} else {
				return false;
			}

		}
	}

}
*/

function geoLocationDatabaseInstalled()
{
	$sql = "SELECT COUNT(*) FROM 202_locations";
	$result = _mysql_query($sql);
	$count = mysql_result($result, 0, 0);
	if ($count != 161877) return false;

	$sql = "SELECT COUNT(*) FROM 202_locations_block";
	$result = _mysql_query($sql);
	$count = mysql_result($result, 0, 0);
	if ($count != 1593228) return false;

	$sql = "SELECT COUNT(*) FROM 202_locations_city";
	$result = _mysql_query($sql);
	$count = mysql_result($result, 0, 0);
	if ($count != 101332) return false;

	$sql = "SELECT COUNT(*) FROM 202_locations_coordinates";
	$result = _mysql_query($sql);
	$count = mysql_result($result, 0, 0);
	if ($count != 125204) return false;

	$sql = "SELECT COUNT(*) FROM 202_locations_country";
	$result = _mysql_query($sql);
	$count = mysql_result($result, 0, 0);
	if ($count != 235) return false;

	$sql = "SELECT COUNT(*) FROM 202_locations_region";
	$result = _mysql_query($sql);
	$count = mysql_result($result, 0, 0);
	if ($count != 396) return false;

	#if no return false
	return true;
}

function getLocationDatabasedOn()
{
	return false;
}

function iphone()
{
	if ($_GET['iphone']) { return true; }
	if(preg_match("/iphone/i",$_SERVER["HTTP_USER_AGENT"])) { return true; } else { return false; }
}


// Lookup payout for provided campaign.
function lookupPayout($pub, $campaign_id)
{
    $s_account = db::escape($pub);
    $s_campaign = db::escape($campaign_id);

    $sql = "
        SELECT IFNULL(p.per_lead, c.street_payout) per_lead, p.per_sale_flat
        FROM prosper_master.campaigns c
        LEFT JOIN prosper_master.payouts p
        ON p.campaign_id = c.id
        AND p.account_id = '{$s_account}'
        WHERE c.id='{$s_campaign}'
    ";
        
    $row = db::getRow($sql);
    
    if ($row['per_sale_flat'] > 0.00)
        return $row['per_sale_flat'];
    else
        return $row['per_lead']; // per_sale_flat, per_sale_percent
}


// Fetch an active creative. Click link in most cases?
// Need this for building click links.
function findCampaignCreative($campaign_id)
{
    $s_campaign = db::escape($campaign_id);
    $row = db::getRow("SELECT * FROM prosper_master.creatives
                       WHERE campaign_id='{$s_campaign}' LIMIT 1");
    return $row['creative_id'];
}
