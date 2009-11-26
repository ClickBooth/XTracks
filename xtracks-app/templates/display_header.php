<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title><? echo $title; ?></title>
    <meta name="description" content="description" />
    <meta name="keywords" content="keywords"/>
    <meta name="copyright" content="Prosper202, Inc" />
    <meta name="author" content="Prosper202, Inc" />
    <meta name="MSSmartTagsPreventParsing" content="TRUE"/>

    <meta name="robots" content="noindex, nofollow" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="imagetoolbar" content="no"/>

    <link rel="shortcut icon" href="/xtracks-img/favicon.ico" type="image/ico"/>
<script type="text/javascript"><!--
var $ = function(arg) {
	return {
	    ready : function(arg){}
	};
};
//--></script>


    <link href="/xtracks-css/account.css" rel="stylesheet" type="text/css"/>
    <link href="/xtracks-css/tracking202.css" rel="stylesheet" type="text/css"/>
    <link href="/xtracks-css/scal.css" rel="stylesheet" type="text/css"/>
    <link href="/xtracks-css/menu.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="/js/tracking202scripts.js"></script>
    <script type="text/javascript" src="/js/call_prefs.js"></script>
    <script type="text/javascript" src="/js/prototype.js"></script>
    <script type="text/javascript" src="/js/scriptaculous/scriptaculous.js"></script>
    <script type="text/javascript" src="/js/scal.js"></script>
    <link href="/xtracks-css/offers202.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="/js/menu.js"></script>
    <link href="/xtracks-css/offers202.css" rel="stylesheet" type="text/css"/>

<style type="text/css">
html,body {
    background-color:#FFFFFF;
}
html {
    height: auto;
}
body {
    min-width: none;
}
h2.green, h3.green {
    font-weight:bold;
}
</style>
<table style="width:100%;"><tbody><tr>
<td><a href="/overview/"><img src="/xtracks-img/logo.png" border="0" /></a></td>
<td style="vertical-align:middle;padding-right:5px;">
<div class="skyline">

                    <a href="http://xtracks.clickbooth.com/" <? if ($navigation[1] == 'setup' && $navigation[2] == 'ppc_accounts.php') { echo 'class="bold";'; } ?>>XTracks</a>
                    &middot;
                    <a href="/xtracks-account/account.php" <? if ($navigation[2] == 'account.php') { echo 'class="bold";'; } ?>>My Account</a>
                    <!--&middot;
                    <a href="/202-account/signout.php">Sign Out</a>
                    -->
    </div>
</td>
</tr></tbody></table>
<table><tbody><tr><td valign="top">
<?php include_once(APP_DIR.'/classes/Navigation.php'); ?>
<style type="text/css">
body {
    width: auto;
    height: auto;
    max-width: none;
    min-width: none;
}
</style>
</td><td style="vertical-align:top; text-align:left; overflow:auto;">

<div class="body">
	<div class="body-content">



	<div class="content"><?php
		if ($navigation[1] == 'tracking202') {  include_once($_SERVER['DOCUMENT_ROOT'] . '/_config/top.php'); }
		if ($navigation[1] == 'tracking202api') {  include_once($_SERVER['DOCUMENT_ROOT'] . '/tracking202api/_config/top.php'); }
?>
