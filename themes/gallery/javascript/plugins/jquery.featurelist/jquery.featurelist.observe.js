ff.cms.fn.featurelist = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
		
	/*css*/
	ff.load("jquery.plugins.featurelist", function() {	
		jQuery(targetid + '.featurelist').each(function() {
			jQuery(targetid + '.featurelist').closest("div,td").append('<div id="' + targetid + 'output"><img src="#" /></div>');
	       jQuery(this).featureList({
				output: "#" + targetid + "output"
				,start_item: 0
				,pause_on_hover: true
				,transition_interval: 500
			});
		});
	});
};