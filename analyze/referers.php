<? include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php'); 

AUTH::require_user();




//set the timezone for the user, for entering their dates.
	AUTH::set_timezone($_SESSION['user_timezone']);

//show the template
template_top('Analyze Incoming Referers',NULL,NULL,NULL); ?>



<div id="info">
	<h2>Analyze Incoming Referers</h2>
</div>                                        

<? display_calendar('/ajax/sort_referers.php', true, true, true, true, true, true); ?> 
    
<script type="text/javascript">
document.observe('dom:loaded', function() {
   loadContent('/ajax/sort_referers.php',null);
});
</script>




<?  template_bottom();
	