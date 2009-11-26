<? include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php'); 

AUTH::require_user();




if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
	$mysql['user_id'] = mysql_real_escape_string($_SESSION['user_id']);
	
      $subids = $_POST['subids']; 
	$subids = trim($subids); 
	$subids = explode("\r",$subids);
	$subids = str_replace("\n",'',$subids);
	
	foreach( $subids as $key => $click_id ) {
		$mysql['click_id'] = mysql_real_escape_string($click_id);
		$update_sql = "UPDATE 202_clicks SET click_lead='1', `click_filtered`='0' WHERE click_id='" . $mysql['click_id'] ."' AND user_id='".$mysql['user_id']."'";
		$update_result = mysql_query($update_sql) or die(mysql_error());
		
		$update_sql = "UPDATE 202_clicks_spy SET click_lead='1', `click_filtered`='0' WHERE click_id='" . $mysql['click_id'] ."' AND user_id='".$mysql['user_id']."'";
		$update_result = mysql_query($update_sql) or die(mysql_error());
		
	} 
	
    	$success = true;
	
	//this deletes all this users cached data to the old result sets, we want new stuff because they just updated old clicks
	//memcache_delete_user_keys();
}

//show the template
template_top('Update Subids'); ?>

<div id="info">
    <h2>Update Your Subids</h2>
	Here is where you can update your income for XTracks, by importing your subids from your affiliate marketing reports.
</div>


    
    <? if ($success == true) { ?>
        <div class="success"><div><h3>Your submission was successful</h3>Your account income now reflects the subids from the commisisons you just uploaded.</div></div>
    <? } ?>
	<div id="m-content">
	<form method="post" action="">
		<table cellpadding="0" cellspacing="1" class="m-stats">    
			<tr>
				<th>Subids</th>
			</tr>
            	<tr valign="top">
				<td><textarea name="subids" style="height: 200px; width: 100%; margin: 0px auto;"><? echo $_POST['subids']; ?></textarea></td>
			</tr>
			<tr>
				<td class="m-row-bottom">
					<input type="submit" value="Update Subids"/>    
				</td> 
            	</tr>
		</table>
	</form> 
   </div>     
<? template_bottom();