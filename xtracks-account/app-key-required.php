<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php');    

AUTH::require_user();

template_top('API Key Required');  ?> 


<div class="big-alert">

	The application you are trying to use requires a valid Stats202 App Key. <br/>
	You may enter in your Application Key by visiting the <a href="/xtracks-account/account.php">My Account</a> tab in XTracks.

</div>

        
<? template_bottom();