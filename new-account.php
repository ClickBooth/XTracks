<?php

require './xtracks-app/bootstrap.php';
require './xtracks-app/install/sys-install.php';

$run_install = false;

$domain = Auth::getDomain();

if (!isset($_SESSION['subdomain_granted'])) {
    forward("/new-subdomain.php");
    exit;
}

// Bail out if this page is accessed directly.
if (!isset($_SESSION['login_user'])) {
    forward("/xtracks-login.php");
    exit;
}

if (!isset($_GET['action'])) {

    $subdomain = $_SESSION['subdomain_granted'];
    $s_subdomain = db::escape($subdomain);

    // Check if we have something running already.
    $row = db::getRow("select id, status from prosper_master.install_jobs
                       where subdomain='{$s_subdomain}'");

    if ($row) {
        $install_id = $row['id'];
    } else {
        db::execute("insert into prosper_master.install_jobs
                    (subdomain) VALUES ('$s_subdomain')");
        $install_id = mysql_insert_id(db::$db_write);
    }
    $run_install = true;
}

if (isset($_GET['action']) && $_GET['action'] == 'check')
{
    $install_id = (int)$_GET['install'];
    $row = db::getRow('select * from prosper_master.install_jobs
                      where id='.(int)$install_id);

    echo json_encode(array('status'=>$row['status'], 'auth'=>$_SESSION['authtoken']));
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Setting up your Prosper202 account...</title>
	<script src="assets/jquery-1.3.2.min.js" type="text/javascript" language="Javascript"></script>
	<style type="text/css">
	<!--

	body {
	    font-family: Arial, Verdana, sans-serif;
	    font-size: 12px;
	    line-height: 150%;
	}

	#message {
	    font-size: 40px;
	    margin-top: 200px;
	    font-family: Helvetica, sans-serif;
	    color: #888;
	}

	-->
	</style>
	<script  type="text/javascript" language="Javascript">
	<!--

	var install = '<?php echo($install_id); ?>';
    var subdomain = '<?php echo($subdomain); ?>';

	function checkStatus(setup)
	{
	    if (setup != 1) {
            $.getJSON("new-account.php?action=check&install="+install, function(data) {
                if (data.status == 'done') {
                    window.location = 'http://'+subdomain+'.<?php echo($domain);?>/setup/ppc_accounts.php?auth='+data.auth;
                }
            });
            setTimeout('checkStatus()', 5000);

        } else {
            setTimeout('checkStatus()', 1000);
        }
	}

	$(document).ready(function() {
	    checkStatus(1);
	});

	-->
	</script>
</head>
<body>

<div align="center" id="message">
One moment while we setup your account.
<br/><br/><br/>
<img src="./assets/ajax-loader.gif"/>

</div>

</body>
</html>
<?php

if ($run_install) {
    flush();
    set_time_limit(90);
    $subdomain = $_SESSION['subdomain_granted'];
    $affiliate_id = $_SESSION['affiliate_id'];
    if (SystemInstaller::makeInstallation($subdomain, $affiliate_id)) {
        SystemInstaller::markDone($install_id, $affiliate_id);
    }
    exit;
}
