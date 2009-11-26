<? include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php'); 


if (!$_GET['subid'] and !$_GET['sid']) die();

$click_id = $_GET['subid'];
if ($_GET['sid']) $click_id = $_GET['sid'];

if (!is_numeric($click_id)) die();

$mysql['click_id'] = mysql_real_escape_string($click_id);

if ($_GET['amount']) $mysql['click_payout'] = mysql_real_escape_string($_GET['amount']);

//ok now update and fire the pixel tracking
$click_sql = "	UPDATE 					202_clicks 
				SET 						click_lead='1', 
											click_filtered='0'  ";
if ($mysql['click_payout']) $click_sql .= " , 	click_payout='".$mysql['click_payout']."' ";
$click_sql .= "	WHERE 	click_id='".$mysql['click_id']."' ";
delay_sql($click_sql);

$click_sql = "	UPDATE 					202_clicks_spy 
				SET 						click_lead='1', 
											click_filtered='0'  ";
if ($mysql['click_payout']) $click_sql .= " , 	click_payout='".$mysql['click_payout']."' ";
$click_sql .= "	WHERE 	click_id='".$mysql['click_id']."' ";
delay_sql($click_sql);