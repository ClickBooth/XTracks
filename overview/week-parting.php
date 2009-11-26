<? include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php'); 

AUTH::require_user();




//show the template
template_top('Hourly Overview',NULL,NULL,NULL);  ?>

<div id="info">
	<h2>Week Parting</h2>
	Here you can see what day of the week performs best.
</div>

<? display_calendar('/ajax/sort_weekly.php', true, true, true, false, true, true); ?>    

<script type="text/javascript">
document.observe('dom:loaded', function() {
   loadContent('/ajax/sort_weekly.php',null);
});
</script>

<? template_bottom();