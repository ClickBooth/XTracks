<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php');

AUTH::require_user();

template_top('Administration',NULL,NULL,NULL);  ?>


<hr/><h2 style="text-align: center;">System Configuration</h2>
<table cellspacing="0" cellpadding="10" style="margin: 0px auto; padding-left: 100px;" >
	<tr>
		<th>Prosper202 Version:</th>
		<td><? echo $version; ?></td>
	</tr>
	<tr>
		<th>PHP Version:</th>
		<td><? echo phpversion(); ?></td>
	</tr>
	<tr>
		<th>MySQL Version:</th>
		<td>
			<? $mysql_version_sql = "SELECT VERSION();";
		     $mysql_version_result = _mysql_query($mysql_version_sql);
		     $mysql_version = mysql_result($mysql_version_result,0,0);
		     $html['mysql_version'] = htmlentities($mysql_version, ENT_QUOTES, 'UTF-8');
		     echo $html['mysql_version'] ; ?>
		</td>
	</tr>
	<tr>
		<th>PHP Safe Mode <a href="#" onclick="alert('PHP Safe Mode needs to be turned off in order for Stats202, Offers202 or Alerts202 to work. You will have to contact your web host to have them disable it.');">[?]</a></th>
		<td><? if (@ini_get('safe_mode'))  	echo '<strong style="color: #900;">On</strong> - this should be turned off.';
				 else 					   		echo 'Off'; ?>
		</td>
	</tr>
	<tr>
		<th>Memcache Installed <a href="#" onclick="alert('If you have memcache installed and working, it will speed up click redirections');">[?]</a></th>
		<td><? if ($memcacheInstalled)  	echo 'Yes';
				 else 					   	echo 'No'; ?>
		</td>
	</tr>
	<tr>
		<th>Memcache Running <a href="#" onclick="alert('If memcache is installed, but not running, check your 202-config.php to make sure your connecting to a server that has memcache installed');">[?]</a></th>
		<td><? if ($memcacheWorking)  	echo 'Yes';
				 else 					   	echo 'No'; ?>
		</td>
	</tr>
	<tr>
		<th>Geo-Location DB Installed <a href="http://prosper202.com/apps/docs/geolocationdb/">[?]</a></th>
		<td><? if (geoLocationDatabaseInstalled() == false) 	echo 'No ';
				else 											echo 'Yes'; ?>
		</td>
	 </tr>
	 <tr>
	 	<th>Default Keyword Preference</th>
	 	<td><?  $mysql['user_id'] = mysql_real_escape_string($_SESSION['user_id']);
			 $user_sql = "SELECT * FROM 202_users_pref WHERE user_id='".$mysql['user_id']."'";
			 $user_result = _mysql_query($user_sql);
			 $user_row = mysql_fetch_assoc($user_result);
			 $html['keyword_pref'] = htmlentities( strtoupper($user_row['user_keyword_searched_or_bidded']) );
			 echo 'Pick up the <strong>'.$html['keyword_pref'].'</strong> keyword <a href="/xtracks-account/account.php">[change]</a>'; ?></td>
	</tr>
	<tr>
		<th>Cached Redirects for MySQL Failure <a href="#" onclick="alert('Make sure this is working, this will make sure your redirects still continue to work in the event of a complete MySQL failure.');">[?]</a></th>
		<td>
			<? if (is_writeable($_SERVER['DOCUMENT_ROOT'].'/redirect/cached/')) echo 'Yes';
			     else 																				echo '<strong style="color: #900;">No</strong>
			     																						   <br/>To enable this CHMOD the directory below to 777:<br/> ' . $_SERVER['DOCUMENT_ROOT'].'/redirect/cached/ '; ?>
</table>

<hr/><h2 style="text-align: center;">PHP settings which affect Export202</h2>
<table cellspacing="0" cellpadding="10" style="margin: 0px auto;" >
	<tr>
		<th> post_max_size:</th>
		<td><? echo ini_get('post_max_size'); ?></td>
	</tr>
	<tr>
		<th> upload_max_filesize:</th>
		<td><? echo ini_get('upload_max_filesize'); ?></td>
	</tr>
	<tr>
		<th> max_input_time:</th>
		<td><? echo ini_get('max_input_time'); ?> (seconds)</td>
	</tr>
	<tr>
		<th> max_execution_time:</th>
		<td><? echo ini_get('max_execution_time'); ?> (seconds)</td>
	</tr>

</table>

<? $click_sql = "SELECT COUNT(*) FROM 202_clicks";
$click_result = _mysql_query($click_sql);
$clicks = mysql_result($click_result,0,0);

?><hr/><h2 style="text-align: center;">Tracking202 Stats</h2><?

echo "<div style=\"text-align: center;\">$clicks clicks recorded to date.</div>";


//show the last 20 logins failed or pass
$user_log_sql = "SELECT * FROM 202_users_log ORDER BY login_id DESC LIMIT 50";
$user_log_result = _mysql_query($user_log_sql);

?><hr/><h2 style="text-align: center;">Last 50 Login Attempts</h2>
<table cellspacing="0" cellpadding="10" style="margin: 0px auto;" >
<tr><th>Time</th><th>Username</th><th>IP Address</th><th>Attempt</th></tr><?
while ($user_log_row = mysql_fetch_assoc($user_log_result)) {

	$html['user_name'] = htmlentities($user_log_row['user_name'], ENT_QUOTES, 'UTF-8');
	$html['ip_address'] = htmlentities($user_log_row['ip_address'], ENT_QUOTES, 'UTF-8');
	$html['login_time'] = htmlentities(date('M d, y \a\t g:ia', $user_log_row['login_time']), ENT_QUOTES, 'UTF-8');

	if ($user_log_row['login_success'] == 0) { $html['login_success'] = '<span style="color: #900;">Failed</span>'; } else { $html['login_success'] = 'Passed'; }

	printf('<tr>
			<td>%s</td>
			<td>%s</td>
			<td>%s :: <a target="_new" href="http://ws.arin.net/whois/?queryinput=%s">ARIN</a> / <a target="_new" href="http://www.db.ripe.net/whois?searchtext=%s">RIPE</a></td>
			<td>%s</td>
		     </tr>',$html['login_time'], $html['user_name'], $html['ip_address'], $html['ip_address'], $html['ip_address'], $html['login_success']);
}
?></table><?php template_bottom();