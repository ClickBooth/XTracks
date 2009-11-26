<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php');

AUTH::require_user();

if ($_GET['edit_aff_campaign_id']) {
	$editing = true;
}

$aff_networks = db::getRows("SELECT * FROM `202_aff_networks`");

if (count($aff_networks) > 1) {
    $user_has_networks = true;
} else {
    $user_has_networks = false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$aff_network_id = trim($_POST['aff_network_id']);
	if (empty($aff_network_id)) {
        $row = db::getRow("SELECT aff_network_id FROM `202_aff_networks` WHERE aff_network_name = 'Clickbooth'");
        $mysql['aff_network_id'] = $row['aff_network_id'];
    }

	$aff_campaign_name = trim($_POST['aff_campaign_name']);
	if (empty($aff_campaign_name)) { $error['aff_campaign_name'] = '<div class="error">What is the name of this campaign.</div>'; }

	$aff_campaign_url = trim($_POST['aff_campaign_url']);
	if (empty($aff_campaign_url)) { $error['aff_campaign_url'] = '<div class="error">What is your affiliate link? Make sure subids can be added to it.</div>'; }

	if ((substr($_POST['aff_campaign_url'],0,7) != 'http://') and (substr($_POST['aff_campaign_url'],0,8) != 'https://')){
      	$error['aff_campaign_url'] .= '<div class="error">Your Landing Page URL must start with http:// or https://</div>';
    }

    $aff_campaign_payout = trim($_POST['aff_campaign_payout']);
	//if (!is_numeric($aff_campaign_payout)) { $error['aff_campaign_payout'] .= '<div class="error">Please enter in a numeric number for the payout.</div>'; }

	//if editing, check to make sure the own the campaign they are editing
	if ($editing == true) {
		$mysql['aff_campaign_id'] = mysql_real_escape_string($_POST['aff_campaign_id']);
		$mysql['user_id'] = mysql_real_escape_string($_SESSION['user_id']);
		$aff_campaign_sql = "SELECT * FROM `202_aff_campaigns` WHERE `user_id`='".$mysql['user_id']."' AND `aff_campaign_id`='".$mysql['aff_campaign_id']."'";
		$aff_campaign_result = mysql_query($aff_campaign_sql) or record_mysql_error($aff_campaign_sql);
		if (mysql_num_rows($aff_campaign_result) == 0 ) {
			$error['wrong_user'] .= '<div class="error">You are not authorized to modify another users campaign</div>';
		}
	}

	if (!$error) {

        $pub = $_SESSION['addCode'];


	    //$campaign_id = (int)$_POST['aff_campaign_id'];
        //$creative_id = (int)findCampaignCreative($campaign_id);

        // Lookup payout for provided campaign.
        $_POST['aff_campaign_payout'] = lookupPayout($pub, $_POST['campaign_id']);

        // Generate link for provided campaign
        //$encrypted = makeEncryptedURL($pub, $creative_id);


		$mysql['aff_campaign_id'] = mysql_real_escape_string($_POST['aff_campaign_id']);
//		$mysql['aff_network_id'] = mysql_real_escape_string($_POST['aff_network_id']);
		$mysql['dt_campaign_id'] = mysql_real_escape_string($_POST['campaign_id']);

		$mysql['aff_campaign_name'] = mysql_real_escape_string($_POST['aff_campaign_name']);
		$mysql['aff_campaign_url'] = mysql_real_escape_string($_POST['aff_campaign_url']);
		$mysql['aff_campaign_url_2'] = mysql_real_escape_string($_POST['aff_campaign_url_2']);
		$mysql['aff_campaign_url_3'] = mysql_real_escape_string($_POST['aff_campaign_url_3']);
		$mysql['aff_campaign_url_4'] = mysql_real_escape_string($_POST['aff_campaign_url_4']);
		$mysql['aff_campaign_url_5'] = mysql_real_escape_string($_POST['aff_campaign_url_5']);
		$mysql['aff_campaign_rotate'] = mysql_real_escape_string($_POST['aff_campaign_rotate']);
		$mysql['aff_campaign_payout'] = mysql_real_escape_string($_POST['aff_campaign_payout']);
		$mysql['aff_campaign_cloaking'] = mysql_real_escape_string($_POST['aff_campaign_cloaking']);
		$mysql['user_id'] = mysql_real_escape_string($_SESSION['user_id']);
		$mysql['aff_campaign_time'] = time();

		if ($editing == true) { $aff_campaign_sql  = "UPDATE `202_aff_campaigns` SET"; }
		else {                  $aff_campaign_sql  = "INSERT INTO `202_aff_campaigns` SET"; }

								$aff_campaign_sql .= "`aff_network_id`='".$mysql['aff_network_id']."',
													  `user_id`='".$mysql['user_id']."',
													  `dt_id`='".$mysql['dt_campaign_id']."',
													  `aff_campaign_name`='".$mysql['aff_campaign_name']."',
													  `aff_campaign_url`='".$mysql['aff_campaign_url']."',
													  `aff_campaign_url_2`='".$mysql['aff_campaign_url_2']."',
													  `aff_campaign_url_3`='".$mysql['aff_campaign_url_3']."',
													  `aff_campaign_url_4`='".$mysql['aff_campaign_url_4']."',
													  `aff_campaign_url_5`='".$mysql['aff_campaign_url_5']."',
													  `aff_campaign_rotate`='".$mysql['aff_campaign_rotate']."',
													  `aff_campaign_payout`='".$mysql['aff_campaign_payout']."',
													  `aff_campaign_cloaking`='".$mysql['aff_campaign_cloaking']."',
													  `aff_campaign_time`='".$mysql['aff_campaign_time']."'";


		if ($editing == true) { $aff_campaign_sql  .= "WHERE `aff_campaign_id`='".$mysql['aff_campaign_id']."'"; }
		$aff_campaign_result = mysql_query($aff_campaign_sql) or record_mysql_error($aff_campaign_sql);
		$add_success = true;



		if ($editing != true) {
			//if this landing page is brand new, add on a landing_page_id_public
			$aff_campaign_row['aff_campaign_id'] = mysql_insert_id();
			$aff_campaign_id_public = rand(1,9) . $aff_campaign_row['aff_campaign_id'] . rand(1,9);
			$mysql['aff_campaign_id_public'] = mysql_real_escape_string($aff_campaign_id_public);
            $mysql['aff_campaign_id'] = mysql_real_escape_string($aff_campaign_row['aff_campaign_id']);

			$aff_campaign_sql = "	UPDATE       `202_aff_campaigns`
								 	SET          	 `aff_campaign_id_public`='".$mysql['aff_campaign_id_public']."'
								 	WHERE        `aff_campaign_id`='".$mysql['aff_campaign_id']."'";
			$aff_campaign_result = mysql_query($aff_campaign_sql) or record_mysql_error($aff_campaign_sql);

        }

	}
}

if (isset($_GET['delete_aff_campaign_id'])) {

	$mysql['user_id'] = mysql_real_escape_string($_SESSION['user_id']);
	$mysql['aff_campaign_id'] = mysql_real_escape_string($_GET['delete_aff_campaign_id']);
	$mysql['date_deleted'] = time();

	$delete_sql = " UPDATE  `202_aff_campaigns`
					SET     `aff_campaign_deleted`='1',
							`aff_campaign_time`='".$mysql['aff_campaign_time']."'
					WHERE   `user_id`='".$mysql['user_id']."'
					AND     `aff_campaign_id`='".$mysql['aff_campaign_id']."'";
	if ($delete_result = mysql_query($delete_sql) or record_mysql_error($delete_result)) {
		$delete_success = true;
	}
}

if ($_GET['edit_aff_campaign_id']) {

	$mysql['user_id'] = mysql_real_escape_string($_SESSION['user_id']);
	$mysql['aff_campaign_id'] = mysql_real_escape_string($_GET['edit_aff_campaign_id']);

	$aff_campaign_sql = "SELECT 	*
						 FROM   	`202_aff_campaigns`
						 WHERE  	`aff_campaign_id`='".$mysql['aff_campaign_id']."'
						 AND    		`user_id`='".$mysql['user_id']."'";
	$aff_campaign_result = mysql_query($aff_campaign_sql) or record_mysql_error($aff_campaign_sql);
	$aff_campaign_row = mysql_fetch_assoc($aff_campaign_result);

	$selected['aff_network_id'] = $aff_campaign_row['aff_network_id'];
	$html = array_map('htmlentities', $aff_campaign_row);
	$html['aff_campaign_id'] = htmlentities($_GET['edit_aff_campaign_id'], ENT_QUOTES, 'UTF-8');
	$html['campaign_id'] = htmlentities($aff_campaign_row['dt_id'], ENT_QUOTES, 'UTF-8');
}

//this will override the edit, if posting and edit fail
if (($_SERVER['REQUEST_METHOD'] == 'POST') and ($add_success != true)) {

	//$selected['aff_network_id'] = $_POST['aff_network_id'];
	$html = array_map('htmlentities', $_POST);
}

template_top('Affiliate Campaigns Setup',NULL,NULL,NULL); ?>

<script type="text/javascript" src="/assets/jsonp.js"></script>
<script type="text/javascript" src="/assets/autocomplete-1.0/autocomplete.js"></script>
<script type="text/javascript">
<!--

var _autocomplete;

document.observe('dom:loaded', function(){
  _autocomplete = new Autocomplete('search_query', { serviceUrl:'campaign_search.php',
    maxHeight:400,
    width:300,
    deferRequestBy:100,
    // callback function:
    onSelect: function(value, data){
        $('campaign_id').value = data;
        return false;
      }
  });

  _autocomplete.getSuggestions = function() {
        var selected_item = $('aff_network_id').options[$('aff_network_id').selectedIndex].innerHTML;
        if (selected_item == 'Clickbooth') {
            updateCampaignField($('search_query').value);
        }
   }
});

var campaign_cache = [];

var jsonp = {
    search_results: function(data)
    {
        var cr = {}
        cr.suggestions = [];
        cr.data = [];

        campaign_cache = data;

        for(var x=0; x < data.length; x++)
        {
            cr.suggestions.push(data[x].campaign_name);
            cr.data.push(data[x].campaign_number);
        }

      _autocomplete.suggestions = cr.suggestions;
      _autocomplete.data = cr.data;
      _autocomplete.suggest($('search_query').value);
    }
}

function updateCampaignField(keyword)
{
    var time = new Date();
    getJSON('http://www.clickbooth.com/publishers/campaigns/search.php?callback=jsonp.search_results'
            +'&_='+time.getTime()+'&keyword='+keyword+'&limit=10&start=0&ajax=1&format=autocomplete');
}


-->
</script>
<style type="text/css">
<!--

.autocomplete-w1 { background:url(/assets/autocomplete-1.0/shadow.png) no-repeat bottom right; position:absolute; top:4px; left:3px; /* IE6 fix: */ _background:none; _top:1px; }
.autocomplete { width:300px; border:1px solid #999; background:#FFF; cursor:default; text-align:left; max-height:350px; overflow:auto; margin:-6px 6px 6px -6px; /* IE specific: */ _height:350px;  _margin:0px 6px 6px 0; overflow-x:hidden; }
.autocomplete .selected { background:#F0F0F0; }
.autocomplete div { padding:2px 5px; white-space:nowrap; }
.autocomplete strong { font-weight:normal; color:#3399FF; }

-->
</style>

<?php 
global $mode;
if ($mode == 'single') {
    $user_id = db::escape($_SESSION['user_id']);
    
    $sql = "
        SELECT pub, pass
        FROM 202_users
        WHERE user_id = '$user_id'
        LIMIT 1
    ";
    $req = db::getRow($sql);
} else {
    $req = array(
        'pub' => $_SESSION['login_user'],
        'token' => $_SESSION['login_pass']
    );
}
$qry = http_build_query($req);
echo "<script src=\"https://www.clickbooth.com/publishers/verify.php?$qry\"></script>";

?>



<div id="info">
    <h2>Affiliate Campaign Setup</h2>
    Here is where you input the affiliate campaigns you are promoting.  <a class="onclick_color" onclick="Effect.toggle('helper','appear')">[help]</a>

    <div style="display: none;" id="helper">
    <br/>Please make sure immediately following your affiliate url you type in that we can insert our subid after it.  If you do not understand how subids work at your network, stop, and contact your affiliate manager about how to add subids to your affiliate links.  You may also contact us and we will help you out as well. <br/><br/>XTracks supports the ability to cloak your traffic, cloaking will prevent your advertisers and the affiliate networks who you work with from seeing your keywords.  Please note if you are doing direct linking with Google Adwords, a cloaked direct linking setup can kill your qualitly score.
    Don't understand cloaking? Leave it off for now and learn more about it in our help section later.</div>
</div>

<table cellspacing="3" cellpadding="3" class="setup">
	<tr valign="top">
        <td>
			<? if ($error) { ?>
				<div class="warning"><div><h3>There were errors with your submission.</h3></div></div>
			<? } echo $error['token']; ?>

			<? if ($add_success == true) { ?>
				<div class="success"><div><h3>Your submission was successful</h3>Your changes were made succesfully.</div></div>
			<? } ?>

			<? if ($delete_success == true) { ?>
				<div class="success"><div><h3>You deletion was successful</h3>You have succesfully removed a campaign.</div></div>
			<? } ?>
			<form method="post" action="<? if ($delete_success == true) { echo $_SERVER['REDIRECT_URL']; }?>" style>
				<input name="aff_campaign_id" type="hidden" value="<? echo $html['aff_campaign_id']; ?>"/>
				<table>
					<tr>
						<td colspan="2">
							<h2 class="green">Add A Campaign</h2>
							<p style="text-align: justify;">Here you add each of the affiliate campaigns you are promoting.</p>
						</td>
					</tr>
					<tr><td/><br/></tr>
					<tr>
						<td class="left_caption">Campaign Name</td>
						<td>
						    <input type="hidden" name="campaign_id" id="campaign_id" value="<? echo $html['campaign_id']; ?>"/>
							<input type="text" name="aff_campaign_name" value="<? echo $html['aff_campaign_name']; ?>" id="search_query" style="display: inline;"/>

                            <?php if ($user_has_networks): ?>
							<select name="aff_network_id" id="aff_network_id">
							<option>--</option>

							<?php

							foreach($aff_networks as $n):

                                if ($n['aff_network_name'] == 'Clickbooth') {
                                    $selected = ' selected="selected"';
                                } else {
                                    $selected = '';
                                }

							?>
							<option value="<?php echo($n['aff_network_id']); ?>"<?php echo($selected); ?>><?php echo($n['aff_network_name']); ?></option>
							<?php endforeach;?>

							</select>
                            <?php endif; ?>

						</td>
					</tr>
					<tr>
						<td class="left_caption">Rotate Urls</td>
						<td>
							<input type="radio" name="aff_campaign_rotate" value="0" onClick="showAllRotatingUrls('false');" <? if ($html['aff_campaign_rotate'] == 0) echo ' CHECKED '; ?>> No
							<span style="padding-left: 10px;"><input type="radio" name="aff_campaign_rotate" value="1" onClick="showAllRotatingUrls('true');" <? if ($html['aff_campaign_rotate'] == 1) echo ' CHECKED '; ?>> Yes</span>

							<script type="text/javascript">
								function showAllRotatingUrls( bool ) {

									if ( bool == 'true') {

										document.getElementById('rotateUrl2').style.display = 'table-row';
										document.getElementById('rotateUrl3').style.display = 'table-row';
										document.getElementById('rotateUrl4').style.display = 'table-row';
										document.getElementById('rotateUrl5').style.display = 'table-row';

									} else {

										document.getElementById('rotateUrl2').style.display = 'none';
										document.getElementById('rotateUrl3').style.display = 'none';
										document.getElementById('rotateUrl4').style.display = 'none';
										document.getElementById('rotateUrl5').style.display = 'none';
									}
								}
							</script>
						</td>
					</tr>
					<tr>
						<td class="left_caption">Affiliate Url <a class="onclick_color" onclick="alert('This your affiliate link for the campaign, example, cpaempire\'s affiliate url would look like this: http://login.tracking101.com/ez/bvqqegfvysyd/ !!!!!YOU MUST MAKE SURE THAT WE CAN RECORD THE SUBID IMMEDIATELY AFTER UR LINK U PROVIDE, OR ELSE IT WILL NOT WORK - This requires you to know how to track subids, if you do not know how to track subids learn it because this program only works by using subids. If you do not know how to trak Subids, ask your affiliate manager before moving forward!!!!!');"> [?] </a></td>
						<td style="white-space: nowrap;">
							<input type="text" name="aff_campaign_url" value="<? echo $html['aff_campaign_url']; ?>" style="width: 200px; display: inline;"/> [subid]
						</td>
					</tr>
					<tr id="rotateUrl2" <? if ($html['aff_campaign_rotate'] == 0) echo ' style="display:none;" '; ?>>
						<td class="left_caption" >Rotate Url #2</td>
						<td><input type="text" name="aff_campaign_url_2" value="<? echo $html['aff_campaign_url_2']; ?>" style="width: 200px; display: inline;"/> [subid]</td>
					</tr>
					<tr id="rotateUrl3" <? if ($html['aff_campaign_rotate'] == 0) echo ' style="display:none;" '; ?>>
						<td class="left_caption" >Rotate Url #3</td>
						<td><input type="text" name="aff_campaign_url_3" value="<? echo $html['aff_campaign_url_3']; ?>" style="width: 200px; display: inline;"/> [subid]</td>
					</tr>
					<tr id="rotateUrl4" <? if ($html['aff_campaign_rotate'] == 0) echo ' style="display:none;" '; ?>>
						<td class="left_caption" >Rotate Url #4</td>
						<td><input type="text" name="aff_campaign_url_4" value="<? echo $html['aff_campaign_url_4']; ?>" style="width: 200px; display: inline;"/> [subid]</td>
					</tr>
					<tr id="rotateUrl5" <? if ($html['aff_campaign_rotate'] == 0) echo ' style="display:none;" '; ?>>
						<td class="left_caption" >Rotate Url #5</td>
						<td><input type="text" name="aff_campaign_url_5" value="<? echo $html['aff_campaign_url_5']; ?>" style="width: 200px; display: inline;"/> [subid]</td>
					</tr>
					<tr>
                        <td class="left_caption">Cloaking</td>
						<td style="white-space: nowrap;">
							<select name="aff_campaign_cloaking">
								<option <? if ($html['aff_campaign_cloaking'] == '0') { echo 'selected=""'; } ?> value="0">Off by default</option>
								<option <? if ($html['aff_campaign_cloaking'] == '1') { echo 'selected=""'; } ?> value="1">On by default</option>
							</select>
						</td>
					</tr>
					<tr>
						<td/>
						<td>
							<input type="submit" value="<? if ($editing == true) { echo 'Edit'; } else { echo 'Add'; } ?>" style="display: inline;"/>
							<? if ($editing == true) { ?>
								<input type="submit" value="Cancel" style="display: inline; margin-left: 10px;" onclick="window.location='/setup/aff_campaigns.php'; return false; "/>
							<? } ?>
						</td>
					</tr>
				</table>
				<? echo $error['aff_network_id']; ?>
				<? echo $error['aff_campaign_name']; ?>
				<? echo $error['aff_campaign_url']; ?>
				<? echo $error['aff_campaign_payout']; ?>
				<? echo $error['wrong_user']; ?>
				<? echo $error['cloaking_url']; ?>
			</form>


		</td>
		<td class="setup-right">
			<h2 class="green">My Campaigns</h2>

			<ul>
			<?  $mysql['user_id'] = mysql_real_escape_string($_SESSION['user_id']);
				$aff_network_sql = "SELECT * FROM `202_aff_networks` WHERE `user_id`='".$mysql['user_id']."' AND `aff_network_deleted`='0' ORDER BY `aff_network_name` ASC";
				$aff_network_result = mysql_query($aff_network_sql) or record_mysql_error($aff_network_sql);
				if (mysql_num_rows($aff_network_result) == 0 ) {
					?><li>You have not added any networks.</li><?
				}

				while ($aff_network_row = mysql_fetch_array($aff_network_result, MYSQL_ASSOC)) {
					$html['aff_network_name'] = htmlentities($aff_network_row['aff_network_name'], ENT_QUOTES, 'UTF-8');
					$url['aff_network_id'] = urlencode($aff_network_row['aff_network_id']);

						//print out the individual accounts per each PPC network
						$mysql['aff_network_id'] = mysql_real_escape_string($aff_network_row['aff_network_id']);
						$aff_campaign_sql = "SELECT * FROM `202_aff_campaigns` WHERE `aff_network_id`='".$mysql['aff_network_id']."' AND `aff_campaign_deleted`='0' ORDER BY `aff_campaign_name` ASC";
						$aff_campaign_result = mysql_query($aff_campaign_sql) or record_mysql_error($aff_campaign_sql);

						while ($aff_campaign_row = mysql_fetch_array($aff_campaign_result, MYSQL_ASSOC)) {

							$html['aff_campaign_name'] = htmlentities($aff_campaign_row['aff_campaign_name'], ENT_QUOTES, 'UTF-8');
							$html['aff_campaign_payout'] = ''; //htmlentities($aff_campaign_row['aff_campaign_payout'], ENT_QUOTES, 'UTF-8');
							$html['aff_campaign_url'] = htmlentities($aff_campaign_row['aff_campaign_url'], ENT_QUOTES, 'UTF-8');
							$html['aff_campaign_id'] = htmlentities($aff_campaign_row['aff_campaign_id'], ENT_QUOTES, 'UTF-8');

							printf('<li>%s &middot; <a href="%s" target="_new" style="font-size: 9px;">link</a> - <a href="?edit_aff_campaign_id=%s" style="font-size: 9px;">edit</a> - <a href="?delete_aff_campaign_id=%s" style="font-size: 9px;">remove</a></li>', $html['aff_campaign_name'], $html['aff_campaign_url'], $html['aff_campaign_id'], $html['aff_campaign_id']);

						}

				} ?>
			</ul>
		</td>
	</tr>
</table>

<? template_bottom();