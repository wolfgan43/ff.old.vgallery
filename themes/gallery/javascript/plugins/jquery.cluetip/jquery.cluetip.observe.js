ff.cms.fn.cluetip = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";	
	/*css*/
	ff.load("jquery.plugins.cluetip", function() {
		jQuery(targetid + '.admin-bar[data-admin]').cluetip({
			activation: 'hover',  
			attribute: 'data-admin',        
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
			closeText: '<img src="' + ff.base_path + '/themes/gallery/javascript/plugins/jquery.cluetip/images/cross.png" alt="close" />',
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
	});
};