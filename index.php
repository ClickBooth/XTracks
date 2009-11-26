<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php');

if (((!isset($_GET['add_code']) || !isset($_GET['pub'])) && !isset($_SESSION['user_id']))) {
	global $mode;
	if ($mode == 'single') {
        header('location: /xtracks-login.php');
	} else {
		header('location: /xtracks-info.php');
	}
    exit;
} else {

	$parts = explode('.', $_SERVER['SERVER_NAME']);
	global $reservedSubDomain;
	if ($parts[0] == 'auth') {
		$dtUser = $_GET['add_code'];
		$dtPass = $_GET['pub'];

		if (substr(strtolower($dtUser),0,2) == 'cd') {
			$whereClause = 'addCode = ';
		} else {
			$whereClause = 'email = ';
		}
		$whereClause .= "'$dtUser'";
		/*$_SESSION['login_user'] = $dtUser;
		$_SESSION['login_pass'] = $dtPass;*/
		$sql = "
		    SELECT affiliate_id
		    FROM prosper_master.affiliates
		    WHERE $whereClause
		";
		$row = db::getRow($sql);

        $_SESSION['affiliate_id'] = $row['affiliate_id'];
        $_SESSION['login_user'] = $_GET['add_code'];
        $_SESSION['login_pass'] = $_GET['pub'];

		$sql = "
		    SELECT subdomain
		    FROM prosper_master.installs
		    WHERE affiliate_id = '{$row['affiliate_id']}'
		";

		$row = db::getRow($sql);
		if (!$row) {

			header('location: new-subdomain.php');
			exit;
		}

		$subdomain = $row['subdomain'];
		$_SESSION['subdomain'] = $subdomain;
		$parts = explode('.', $_SERVER['SERVER_NAME']);
		$parts[0] = $subdomain;
		$serverName = implode('.', $parts);

		header("location: http://$serverName/index.php?add_code={$dtUser}&pub={$dtPass}");
	} else {
		$_SESSION['login_user'] = $_GET['add_code'];
        $_SESSION['login_pass'] = $_GET['pub'];
        session_commit();
        header("location: /setup/ppc_accounts.php");
        exit;
	}
}
