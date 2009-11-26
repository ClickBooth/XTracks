
<div style="padding: 10px 0px;"></div>

<div id="nav-primary">  
	<ul name="navbar">
		<li class="<? if ($navigation[2] == 'setup') { echo 'on'; } ?>"><a href="/stats202/setup/" name="setup">Setup</a></li>
		<li class="<? if ($navigation[2] == 'earnings') { echo 'on'; } ?>"><a href="/stats202/earnings/" name="setup">Network Earnings</a></li>
		<li class="<? if ($navigation[2] == 'offers') { echo 'on'; } ?>"><a href="/stats202/offers/" name="jobs">Offer Stats</a></li>
		<li class="<? if ($navigation[2] == 'subids') { echo 'on'; } ?>"><a href="/stats202/subids/" name="visitors">Subid Stats</a></li>      
		<li class="<? if ($navigation[2] == 'postback') { echo 'on'; } ?>"><a href="/stats202/postback/" name="spy">Postback URLs</a></li>
		<!--<li class="core <? if ($navigation[2] == 'help') { echo 'on'; } ?>"><a href="/stats202/help/" name="help">Help</a></li>-->
	</ul>
  </div>
 
  <div id="nav-secondary" <? if (($navigation[2] == 'help')) { echo ' class="core" '; } ?>>
	  <div>
	  	<? if ($navigation[2] == 'setup') { $nav = true; ?>
			<ul>
				<li <? if (!$navigation[3]) { echo 'class="on"'; } ?>><a href="/stats202/setup/">Manage Accounts</a></li>
				<li <? if ($navigation[3] == 'new') { echo 'class="on"'; } ?>><a href="/stats202/setup/new/">Add New Account</a></li> 
			</ul>
		<? } ?>
		
		<? if (!$nav) echo "<ul><li></li></ul>"; ?>
	</div>
</div>


<? /*
<div id="info" style="clear:both;">
	<? switch ($navigation[2]) { 
		case "earnings":
			echo "	<h2>Network Earnings</h2>
					Here you can see your global network earnings. ";
			break;
		case "offers":
			echo "	<h2>Offer Stats</h2>
					Here you can see how well your individual offers are performing.";
			break;
		case "subids":
			echo "	<h2>Subid Stats</h2>
					This shows all of your converted subid stats.  Please note this only shows converted subids. ";
			break;
		case "postback":
			echo "	<h2>Postback URL Setup/h2>
					Here you can add your global postback urls to Stats202.";
			break;
		case "help":
			echo "	<h2>Stats202 Help</h2>
					Here is some information to help you use Stats202.";
			break;
	} ?>
</div>*/ ?>

<br/>