function getStats(page, order, by) { 
	
	if($('s-status-loading')) {   $('s-status-loading').style.display='';       }
     	if($('m-content')) {          $('m-content').className='transparent_class'; } 
	 
	new Ajax.Updater('m-content', '/stats202/ajax/getStats.php', {
	  
      parameters: { page:page, order:order, by:by },
      onSuccess: function() {
         	if($('s-status-loading')) {   $('s-status-loading').style.display='none';   }
            if($('m-content')) {          $('m-content').className=''; }    
      }
    });
}


function getSubids(page, order, by) { 
	
	if($('s-status-loading')) {   $('s-status-loading').style.display='';       }
     	if($('m-content')) {          $('m-content').className='transparent_class'; } 
	
	new Ajax.Updater('m-content', '/stats202/ajax/getSubids.php', {
	  
      parameters: { page:page, order:order, by:by },
      onSuccess: function() {
         	if($('s-status-loading')) {   $('s-status-loading').style.display='none';   }
            if($('m-content')) {          $('m-content').className=''; }    
      }
    });
}




function getOfferStats(page, order, by) { 
	
	if($('s-status-loading')) {   $('s-status-loading').style.display='';       }
     	if($('m-content')) {          $('m-content').className='transparent_class'; } 
	
	new Ajax.Updater('m-content', '/stats202/ajax/getOfferStats.php', {
	  
      parameters: { page:page, order:order, by:by },
      onSuccess: function() {
         if($('s-status-loading')) {   $('s-status-loading').style.display='none';   }
         if($('m-content')) {          $('m-content').className=''; }    
      }
    });
}



//this lights up a row
function lightUpRow(element) { 
	element.style.background = '#FFFFD9';	
}
 
//this dims down a row
function dimDownRow(element, color) { 

	if (!color) { color = 'white'; } 

	element.style.background = color; 
}