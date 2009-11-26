<? include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php'); 

AUTH::require_user();

//show the template
template_top('Account Overview',NULL,NULL,NULL);   ?>

<div id="info" style="clear:both;">
	<h2>Account Overview Screen</h2>
	The account overview screen gives you a quick glance at how all of your campaigns are currently performing.  
</div>
                            


<? display_calendar('/ajax/account_overview.php', true, false, true, false, true, true);    ?>


<script type="text/javascript">
document.observe('dom:loaded', function() {
 loadContent('/ajax/account_overview.php',null);
});
</script>

<? template_bottom();