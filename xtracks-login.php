<? include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {
        AUTH::login($_POST['user_name'], $_POST['user_pass']);
        //redirect to account screen
        header('location: /overview/');

    } catch (Exception $e) {
        $error['user'] = sprintf('<div class="error">%s</div>', $e->getMessage());
    }

	$html['user_name'] = htmlentities($_POST['user_name'], ENT_QUOTES, 'UTF-8');
}

info_top();
?>
	<form method="post" action="">
		<input type="hidden" name="token" value="<? echo $_SESSION['token']; ?>"/>
		<table cellspacing="0" cellpadding="5" style="margin: 0px auto;" >
			<? if ($error['token']) { printf('<tr><td colspan="2">%s</td></tr>', $error['token']); } ?>
			<tr>
				<td>Username:</td>
				<td><input id="user_name" type="text" name="user_name" value="<? echo $html['user_name']; ?>"/></td>
			</tr>
			<? if ($error['user']) { printf('<tr><td colspan="2">%s</td></tr>', $error['user']); } ?>
			<tr>
				<td>Password:</td>
				<td>
					<input id="user_pass" type="password" name="user_pass"/>
					<span id="forgot_pass">(<a href="/202-lost-pass.php">I forgot my password/username</a>)</a>
				</td>
			</tr>
			<tr>
				<td/>
				<td><input id="submit" type="submit" value="Sign In"/></td>
			</tr>
		</table>
	</form>
<? info_bottom(); ?>