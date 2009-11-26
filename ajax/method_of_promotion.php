<? include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php'); 

AUTH::require_user();


  ?>

<select name="method_of_promotion" id="method_of_promotion" onchange="tempLoadMethodOfPromotion(this);">
	<option value="0"> -- </option>
	<option <? if ($_POST['method_of_promotion'] == 'directlink') { echo 'selected=""'; } ?> value="directlink">Direct Linking</option>
	<option <? if ($_POST['method_of_promotion'] == 'landingpage') { echo 'selected=""'; } ?> value="landingpage">Landing Page</option>
</select>