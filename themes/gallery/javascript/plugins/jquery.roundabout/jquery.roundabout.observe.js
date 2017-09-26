ff.cms.fn.roundabout = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";	
	/*css*/
	
	ff.load("jquery.plugins.roundabout", function() {
		jQuery(targetid + ' .roundabout').closest("ul").roundabout({
			 bearing : 0.0,
			 tilt : 0.0,
			 minZ : 100,
			 maxZ : 100,
			 minOpacity : 0.4,
			 maxOpacity : 1.0,
			 minScale : 0.4,
			 maxScale : 1.0,
			 duration : 600,
			 btnNext : null,
			 btnPrev : null,
			 easing : 'swing',
			 clickToFocus : true,
			 focusBearing : true,
			 shape : 'lazySusan',
			 debug : false,
			 childSelector : 'li',
			 startingChild : 0, 
			 reflect : true
		});
	});
};