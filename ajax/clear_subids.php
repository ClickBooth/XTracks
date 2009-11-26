<? include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php'); 

AUTH::require_user();




	$mysql['user_id'] = mysql_real_escape_string($_SESSION['user_id']);
	
	if ($_POST['aff_network_id'] == 0) { $error['clear_subids'] = '<div class="error">You have to at least select an affiliate network to clear out</div>'; }
	$mysql['aff_network_id'] = mysql_real_escape_string($_POST['aff_network_id']);
	
	if ($error){ 
		echo $error['clear_subids'];  
		die();
	}
	
	
	if (!$error) { 

		if ($_POST['aff_campaign_id'] != 0) { 
			
			$mysql['aff_campaign_id'] = mysql_real_escape_string($_POST['aff_campaign_id']);
			$click_sql = "UPDATE 202_clicks SET click_lead=0 WHERE user_id='".$mysql['user_id']."' AND aff_campaign_id='".$mysql['aff_campaign_id']."'";
		
		} else {
		
			$click_sql = "UPDATE 202_clicks LEFT JOIN 202_aff_campaigns USING (aff_campaign_id) LEFT JOIN 202_aff_networks USING (aff_network_id) SET click_lead=0 WHERE 202_clicks.user_id='".$mysql['user_id']."' AND 202_clicks.aff_network_id='".$mysql['aff_network_id']."'";
		}
	
		$click_result = mysql_query($click_sql);
		$clicks = mysql_affected_rows();
		
		if ($clicks < 0 ) { $clicks = 0; }
	
		echo "<div class=\"success\"><div><h3>You have reset <strong>$clicks</strong> subids!</h3>You can now re-upload your subids.</div></div>";
		
	}