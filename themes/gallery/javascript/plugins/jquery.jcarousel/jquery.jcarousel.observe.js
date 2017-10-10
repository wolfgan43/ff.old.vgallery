ff.cms.fn.jcarousel = function(targetid) { 	
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	jQuery(targetid + '.jcarousel').closest('UL').hide();
	ff.pluginLoad("jquery.jcarousel", "/themes/library/plugins/jquery.jcarousel/jquery.jcarousel.js", function() { 
	    jQuery(targetid + '.jcarousel').closest('UL').show().jcarousel({
			start: 2,
			auto: 3,
        		wrap: 'last',
			initCallback: function() {
				carousel.buttonNext.bind('click', function() {
					carousel.startAuto(0);
				});
				carousel.buttonPrev.bind('click', function() {
					carousel.startAuto(0);
				});
				/* Pause autoscrolling if the user moves with the cursor over the clip. */
				carousel.clip.hover(function() {
					carousel.stopAuto();
				}, function() {
					carousel.startAuto();
				});
			}
		});
	}, false);
};