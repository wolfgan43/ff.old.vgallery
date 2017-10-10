ff.cms.fn.jjmenu = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";	
	/*css*/
	
	ff.pluginLoad("jquery.jjmenu", "/themes/library/plugins/jquery.jjmenu/jjmenu.js", function() {
		jQuery(targetid + 'input[type="hidden"].jjmenu').each(function() {
			jQuery(this).closest("div,li,td").attr("admin", jQuery(this).val());
			
			
			
			
			
			
			jQuery(this).closest("div,li,td").cluetip({
			    activation: 'hover',  
			    attribute: 'admin',        
			    cluetipClass: 'jtip', 
			    width: '100%', 
			    titleAttribute:   'title',  
				positionBy: 'auto', 
				topOffset : 0,
				leftOffset: 0,
				showTitle: false,
				arrows: true,
				dropShadow: false,
				hoverIntent: true,
				waitImage : false,
				sticky: true,
				mouseOutClose: true,
				/*delayedClose: 2000, */
				closePosition: 'title',
				closeText: '<img src="' + ff.site_path + '/themes/gallery/javascript/plugin/jquery.jjmenu/images/cross.png" alt="close" />',
				fx: {             
			        open:       'fadeIn', /* can be 'show' or 'slideDown' or 'fadeIn'*/
			        openSpeed:  ''
			    },
				hoverIntent: {    
			        sensitivity:  1,
			        interval:     500,
			        timeout:      0
			    }, 
			    onActivate:       function(e) {
			        return true;
			    }
			});
			jQuery(this).remove();
		});
	}, false);
};