ff.cms.fn.kwicks = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	/*css*/
	ff.load("jquery.plugins.kwicks", function() {	
		jQuery(targetid + '.kwicks').each(function() {
			
			var kwicks_counter = 0;
			jQuery(this).children('li').each(function(i) {	
				if(jQuery(this).hasClass('current')) {
					kwicks_counter = i;
					return;
				}
			});
			
	       jQuery(this).kwicks({
						max : 220,
						duration : 100,
						sticky : true,
						defaultKwick : kwicks_counter,
						spacing : 5
			});
		});
	});
};