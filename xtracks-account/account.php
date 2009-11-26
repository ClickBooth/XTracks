<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php');

AUTH::require_user();

$update_cb_login = false;

if (isset($_POST['action']) && $_POST['action'] == 'change_affiliate_info')
{
    $s_user = db::escape($_POST['cb_username']);
    $s_pass = db::escape($_POST['cb_password']);
    $s_uid = db::escape($_SESSION['user_id']);

    $ok = db::execute("UPDATE 202_users
                 SET cb_user='{$s_user}', cb_pass='{$s_pass}'
                 WHERE user_id='{$s_uid}'");
}
else
{
    // fixme: push this to an ajax file
    if(isset($_REQUEST['action']))
    {
        // ghetto-switch time
        switch($_REQUEST['action'])
        {
        case "add_affnet":
            $mysql['user_id'] = mysql_real_escape_string($_SESSION['user_id']);
            $mysql['name']    = mysql_real_escape_string($_REQUEST['affnet_id']);
            if(db::execute("INSERT INTO 202_aff_networks (user_id, aff_network_name) VALUES ('".$mysql['user_id']."', '".$mysql['name']."')"))
                $result = db::getRow("SELECT aff_network_name as name, aff_network_id as id FROM 202_aff_networks WHERE aff_network_name = '".$mysql['name']."'");
            else
                $result = "error";  // fancy
            break;

        case "delete_affnet":
            $mysql['user_id'] = mysql_real_escape_string($_SESSION['user_id']);
            $mysql['id']    = mysql_real_escape_string($_REQUEST['affnet_id']);
            $result = db::execute("DELETE FROM 202_aff_networks WHERE user_id = '".$mysql['user_id']."' AND aff_network_id = '".$mysql['id']."'");
            break;
        }

        echo json_encode($result);
        exit;
    }
}

//get all of the user data
$mysql['user_id'] = mysql_real_escape_string($_SESSION['user_id']);
$user_sql = "	SELECT 	*
				 FROM   	`202_users`
				 LEFT JOIN  `202_users_pref` USING (user_id)
				 WHERE  	`202_users`.`user_id`='".$mysql['user_id']."'";

$user_result = _mysql_query($user_sql);
$user_row = mysql_fetch_assoc($user_result);
$html = array_map('htmlentities', $user_row);

//make it hide most of the api keys
$hideChars = 22;
for ($x = 0; $x < $hideChars; $x++) $hiddenPart .= '*';
if ($html['user_api_key']) $html['user_api_key'] = $hiddenPart . substr($html['user_api_key'], $hideChars, 99);
if ($html['user_stats202_app_key']) $html['user_stats202_app_key'] = $hiddenPart . substr($html['user_stats202_app_key'], $hideChars, 99);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($_POST['update_profile'] == '1') {
		if ($_POST['token'] != $_SESSION['token']){
		    $error['token'] = '<div class="error">You must use our forms to submit data.</div';
        }

		switch ($_POST['user_keyword_searched_or_bidded']) {
			case "searched":
			case "bidded":
				break;
			default:
				$error['user_keyword_searched_or_bidded'] = '<div class="error">You must select your keyword preference.</div>';
				break;
		}

		if (!$error) {

			$mysql['user_id'] = mysql_real_escape_string($_SESSION['user_id']);
	        	$mysql['user_timezone'] = mysql_real_escape_string($_POST['user_timezone']);
			$mysql['user_keyword_searched_or_bidded'] = mysql_real_escape_string($_POST['user_keyword_searched_or_bidded']);

			$user_sql = "	UPDATE 	`202_users`
							 SET `user_timezone`='".$mysql['user_timezone']."'
						 	WHERE  	`user_id`='".$mysql['user_id']."'";
	       	 $user_result = _mysql_query($user_sql);

	       	 $user_sql = "UPDATE 	`202_users_pref`
	       	   			   SET 		`user_keyword_searched_or_bidded`='".$mysql['user_keyword_searched_or_bidded']."'
	       	   			   WHERE	`user_id`='".$mysql['user_id']."'";
	       	 $user_result = _mysql_query($user_sql);

	       	 $update_profile = true;

	        	//set the  session's user_timezone
			$_SESSION['user_timezone'] = $_POST['user_timezone'];
		}
	}

	$html = array_merge($html, array_map('htmlentities', $_POST));
}


$html['user_id'] = htmlentities($_SESSION['user_id'], ENT_QUOTES, 'UTF-8');
$html['user_username'] = htmlentities($_SESSION['user_username'], ENT_QUOTES, 'UTF-8');

template_top('User Profile',NULL,NULL,NULL);  ?>

<script src="../assets/jquery-1.3.2.min.js" type="text/javascript" language="Javascript"></script>
<style>
.my-account-tables { margin: 0px auto; }
.my-account-tables td { width: 150px; }
</style>
<script type="text/javascript" language="Javascript">
<!--
jQuery.noConflict();

function addAffNetwork()
{
    var affnet_name = jQuery('#new_aff_network').val();

    jQuery.getJSON('account.php?affnet_id='+affnet_name+'&action=add_affnet', function(data) {
        jQuery('#new_aff_network').val('');
        jQuery('#networks').append("<span id=\"affnet_link_"+data.id+"\">"+data.name+"<a href=\"#\" onClick=\"return removeAffNetwork('"+data.id+"');\">Remove</a><br/></span>");
    });

    return false;
}

function removeAffNetwork(id)
{
    jQuery.get('account.php?affnet_id='+id+'&action=delete_affnet', function(data) {
        jQuery('#affnet_link_'+id).remove();
    });

    return false;
}

-->
</script>

<form method="post"  action="" enctype="multipart/form-data">
    <input type="hidden" name="update_profile" value="1" />
    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
    <table class="my-account-tables"  cellpadding="5" cellspacing="0">
        <tr>
            <td colspan="2">
            	<h2 class="green">My Account</h2>
                <p class="first bold">Here you can modify your account settings. Required fields marked with by *</p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <? if ($update_profile == true) { ?>
                    <div class="success"><div><h3>Your submission was successful</h3>Your changes were made succesfully.</div></div>
                <? } ?>
				<? echo $error['token'] . $error['user_email'] . $error['user_keyword_searched_or_bidded'] ; ?>
            </td>
        </tr>
        <tr>
            <td class="left_caption">Time zone (GMT) *</td>
            <td>
			<select name="user_timezone">
				<option <? if ($html['user_timezone'] == '-11') { echo 'selected=""'; } ?> value="-11">-1100 : Samoa</option>
				<option <? if ($html['user_timezone'] == '-10') { echo 'selected=""'; } ?> value="-10">-1000 : Alaska, Hawai'i</option>
				<option <? if ($html['user_timezone'] == '-9') { echo 'selected=""'; } ?> value="-9">-0900 : </option>
				<option <? if ($html['user_timezone'] == '-8') { echo 'selected=""'; } ?>  value="-8">-0800 : US Pacific</option>
				<option <? if ($html['user_timezone'] == '-7') { echo 'selected=""'; } ?> value="-7">-0700 : US Mountain</option>
				<option <? if ($html['user_timezone'] == '-6') { echo 'selected=""'; } ?> value="-6">-0600 : US Central</option>
				<option <? if ($html['user_timezone'] == '-5') { echo 'selected=""'; } ?> value="-5">-0500 : US Eastern</option>
				<option <? if ($html['user_timezone'] == '-4') { echo 'selected=""'; } ?> value="-4">-0400 : Atlantic</option>
				<option <? if ($html['user_timezone'] == '-3.5') { echo 'selected=""'; } ?> value="-3.5">-0350 : Newfoundland</option>
				<option <? if ($html['user_timezone'] == '-3') { echo 'selected=""'; } ?> value="-3">-0300 : Brazil, Argentina</option>
				<option <? if ($html['user_timezone'] == '-2') { echo 'selected=""'; } ?> value="-2">-0200 : Mid Atlantic</option>
				<option <? if ($html['user_timezone'] == '0') { echo 'selected=""'; } ?> value="0">+0000 : London, Dublin</option>
				<option <? if ($html['user_timezone'] == '1') { echo 'selected=""'; } ?> value="1">+0100 : Paris, Berlin, Amsterdam, Madrid</option>
				<option <? if ($html['user_timezone'] == '2') { echo 'selected=""'; } ?> value="2">+0200 : Athens, Istanbul, Helsinki</option>
				<option <? if ($html['user_timezone'] == '3') { echo 'selected=""'; } ?> value="3">+0300 : Kuwait, Moscow</option>
				<option <? if ($html['user_timezone'] == '3.5') { echo 'selected=""'; } ?> value="3.5">+0350 : Tehran</option>
				<option <? if ($html['user_timezone'] == '5.5') { echo 'selected=""'; } ?> value="5.5">+0530 : India</option>
				<option <? if ($html['user_timezone'] == '7') { echo 'selected=""'; } ?> value="7">+0700 : Bangkok</option>
				<option <? if ($html['user_timezone'] == '7.5') { echo 'selected=""'; } ?> value="7">+0700 : </option>
				<option <? if ($html['user_timezone'] == '8') { echo 'selected=""'; } ?> value="8">+0800 : Hong Kong</option>
				<option <? if ($html['user_timezone'] == '9') { echo 'selected=""'; } ?> value="9">+0900 : Tokyo</option>
				<option <? if ($html['user_timezone'] == '9.5') { echo 'selected=""'; } ?> value="9.5">+0950 : Darwin</option>
				<option <? if ($html['user_timezone'] == '10') { echo 'selected=""'; } ?> value="10">+1000 : Sydney</option>
				<option <? if ($html['user_timezone'] == '11') { echo 'selected=""'; } ?> value="11">+1100 : Magadan</option>
				<option <? if ($html['user_timezone'] == '12') { echo 'selected=""'; } ?> value="12">+1200 : Wellington</option>
                </select>
            </td>
        </tr>

        <tr>
		<td class="left_caption">Keyword Pref *</td>
		<td><select name="user_keyword_searched_or_bidded">
				<option <? if ($html['user_keyword_searched_or_bidded'] == 'searched') { echo 'selected=""'; } ?> value = "searched">Pickup Searched Keyword</option>
				<option <? if ($html['user_keyword_searched_or_bidded'] == 'bidded') { echo 'selected=""'; } ?> value = "bidded">Pickup Bidded Keyword</option>
			  </select>
		</td>
        </tr>
        <tr>
            <td><input class="submit"  type="submit" value="Update Profile"/></td>
        </tr>
	</table>
</form>

<?php if(Config::getInstance()->install_mode == 'single'): ?>
<form method="post" action="">
<input type="hidden" name="action" value="change_affiliate_info">
<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
<table class="my-account-tables" style="margin-top: 30px; width: 450px;" cellpadding="5" cellspacing="0" border="0">
    <tr>
        <td colspan="2">
            <h2 class="green">Clickbooth Account</h2>
            <p class="first bold" >For access to live Clickbooth affiliate data, enter in your CB username and password below.</p>
        </td>
    </tr>
    <?php if ($update_cb_login): ?>
        <tr>
            <td colspan="2">
                    <div class="success"><div><h3>Your submission was successful</h3>Your changes were made succesfully.</div></div>
				<?php echo $error['token'] . $error['cb_login']; ?>
            </td>
        </tr>
        <?php endif; ?>
	 <tr>
		<td class="left_caption" style="width:90px;">Clickbooth Username:</td>
		<td><input type="text"  style="width:200px;" name="cb_username" value="<?php echo $html['cb_username']; ?>"/></td>
        </tr>
	 <tr>
		<td class="left_caption">Clickbooth Password:</td>
		<td><input type="text" style="width:200px;" name="cb_password" value="<?php echo $html['cb_password']; ?>"/></td>
        </tr>
        <tr>
            <td colspan="2"><input class="submit"  type="submit" value="Update Account"/></td>
        </tr>
</table>
</form>
<?php endif; ?>

<form method="post" action="">
	<input type="hidden" name="change_affiliate_networks" value="1" />
    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
    <table class="my-account-tables" style="margin-top: 30px; width: 450px;" cellpadding="5" cellspacing="0" border="0">
        <tr>
            <td colspan="2">
				<h2 class="green">Add Affiliate Networks</h2>
				<p class="first bold" >If you wish to add other affiliate networks for tracking, use the form below.</p>
            </td>
		</tr>
        <tr>
            <td colspan="2">
				<? if ($change_affiliate_networks == true) { ?>
					<div class="success"><div><h3>Your submission was successful</h3>Your changes were made succesfully.</div></div>
                <? } ?>
                <? echo $error['token']; ?>
				<? echo $error['affiliate_network']; ?>
            </td>
        </tr>
        <tr>
            <td valign="top" style="width: 80px;">Existing Networks:</td>
            <td valign="top" id="networks">
            <?php
            $rows = db::getRows("SELECT aff_network_id, aff_network_name FROM 202_aff_networks WHERE aff_network_deleted = 0");
            foreach($rows as $row):
            ?>
                <span id="affnet_link_<?php echo($row['aff_network_id']);?>">
                <?php echo($row['aff_network_name']); ?>
                <a href="#" onClick="return removeAffNetwork(<?php echo($row['aff_network_id']); ?>);">
                Remove</a><br/></span>

            <?php endforeach; ?>
            </td>
        </tr>
        <tr>
			<td valign="top" class="left_caption" style="width: 80px;">Add Affiliate Network</td>
            <td valign="top"><input type="text" name="new_aff_network" id="new_aff_network"/><br/>
                            <input class="submit" type="button" value="Add Network" onClick="addAffNetwork(); return false;"/></td>
        </tr>
    </table>
</form>


<?php template_bottom();
