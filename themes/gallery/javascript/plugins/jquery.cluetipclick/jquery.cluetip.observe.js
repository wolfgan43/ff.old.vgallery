ff.cms.fn.cluetipclick = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	ff.load(["jquery.plugins.rightclick", "jquery.plugins.cluetip"], function() {
		jQuery(targetid + '.admin-bar[data-admin]').cluetip({
			activation: 'rightClick', 
			attribute: 'data-admin',        
			cluetipClass: 'jtip', 
			width: '100%', 
			titleAttribute:   'title',  
			positionBy: 'mouse',
			showTitle: false,
			arrows: false,
			dropShadow: false,
			hoverIntent: true,
			waitImage : true,
			sticky: true,
			mouseOutClose: true,
			closePosition: 'title',
			closeText: '<img src="' + ff.base_path + '/themes/gallery/javascript/plugins/jquery.cluetipclick/images/cross.png" alt="close" />',
			fx: {             
				open:       'fadeIn', /* can be 'show' or 'slideDown' or 'fadeIn'*/
				openSpeed:  '1000'
			},
			hoverIntent: {    
				sensitivity:  1,
				interval:     400,
				timeout:      1
			}, 
			onActivate:       function(e) {
				return true;
			}
		});
	});
};