<?php

require './xtracks-app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $subdomain = preg_replace('/[^a-zA-Z0-9]+/', '', strip_tags($_POST['subdomain']));
    $_SESSION['subdomain_granted'] = $subdomain;
    $domain = Auth::getDomain();

    forward("http://auth.{$domain}/new-account.php");
    exit;
}

if (isset($_GET['subdomain'])) {
	global $skipTemplate;
	$skipTemplate = true;

    $s_subdomain = db::escape(preg_replace('/[^a-zA-Z0-9]+/', '', $_GET['subdomain']));

    $row = db::getRow("select 1 from prosper_master.install_jobs WHERE subdomain='{$s_subdomain}'");

    if ($row) {
        echo json_encode(array('available'=>false));
    } else {
        echo json_encode(array('available'=>true));
    }
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Select a subdomain</title>
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

	label {
	    font-size: 14px;
	    color: #333;
	}

	#subdomain.error {
	    background-color: #FFA4A4;
	}

	-->
	</style>
	<script  type="text/javascript" language="Javascript">
	<!--

	function checkSubdomain()
	{
	    var subdomain = $('#subdomain').val();

	    $.getJSON('new-subdomain.php?subdomain='+subdomain, function(reply) {
            if (reply.available) {
                $('#check_button').attr('disabled', 'disabled');
                $('#get_button').removeAttr('disabled');
            } else {
                $('#check_button').removeAttr('disabled');
                $('#get_button').attr('disabled', 'disabled');
            }
	    });
	}

	$(document).ready(function() {
	    $('#subdomain').focus();
	});

	-->
	</script>
</head>
<body>

<div align="center" id="message">
Choose your subdomain.
<br/><br/><br/>
<form method="POST" action="new-subdomain.php">
<label>Subdomain:</label> <input type="text" id="subdomain" name="subdomain"/>
<input type="button" id="check_button" value="check" onClick="checkSubdomain();"/>
<input type="submit" value="get subdomain" id="get_button" disabled="disabled" />
</form>
</div>

</body>
</html>
