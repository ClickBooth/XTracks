<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php';

AUTH::require_user();
global $dbname;

$userId = mysql_real_escape_string($_SESSION['user_id']);

$sql = "
    SELECT aff_network_id id, aff_network_name name
    FROM `202_aff_networks`
    WHERE `user_id`='$userId'
    AND `aff_network_deleted`='0'
    ORDER BY `aff_network_name` ASC
";

$res = mysql_query($sql) or record_mysql_error($sql);

$networks = array();

while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
	$networks[$row['id']] = $row['name'];
}
if (count($networks) <= 1) {
	foreach($networks as $id => $name) {
		$id = htmlentities($id, ENT_QUOTES, 'UTF-8');
		if ($name == 'Clickbooth') {
			$name = '<span style="font-weight:bold;">'
			    .htmlentities($name, ENT_QUOTES, 'UTF-8')
			    .'</span>';
		} else {
		    $name = htmlentities($name, ENT_QUOTES, 'UTF-8');
		}

		echo "$name<input type=\"hidden\" name=\"aff_network_id\" value=\"$id\"/>";
		break;
	}
} else {
	$networkOpts = array();
	foreach($networks as $id => $name) {
		$selected = '';
		if (isset($_POST['aff_network_id']) && $_POST['aff_network_id'] == $id) {
			$selected = ' selected';
		}
		$id = htmlentities($id, ENT_QUOTES, 'UTF-8');
		$name = htmlentities($name, ENT_QUOTES, 'UTF-8');
		$networkOpts[]= "<option value=\"$id\"$selected>$name</option>";
	}
	$networkOpts = implode('', $networkOpts);
	echo <<< DDL
<select name="aff_network_id" id="aff_network_id" onchange="load_aff_campaign_id(this.value, 0);">
    <option value="0"> -- </option>
    $networkOpts
</select>
DDL;
}
