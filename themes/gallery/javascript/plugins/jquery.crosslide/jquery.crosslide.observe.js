ff.cms.fn.crosslide = function(targetid) { 
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	ff.load("jquery.plugins.crosslide", "/themes/library/plugins/jquery.crosslide/jquery.crosslide.js", function() {
		var arrCrosSlide = new Array();
		
		jQuery(targetid + '.crosslide').each(function() {
			if(jQuery(this).parent().attr("id") != '') {
				if(arrCrosSlide[jQuery(this).parent().attr("id")] === undefined)
					arrCrosSlide[jQuery(this).parent().attr("id")] = '';
			
				if(arrCrosSlide[jQuery(this).parent().attr("id")] != '')
					arrCrosSlide[jQuery(this).parent().attr("id")] = arrCrosSlide[jQuery(this).parent().attr("id")] + ',';
					
				arrCrosSlide[jQuery(this).parent().attr("id")] = arrCrosSlide[jQuery(this).parent().attr("id")] + "{ src: '" + jQuery(this).attr('src') + "' }";
			}
		});
		
		 for (var crossSlide in arrCrosSlide) { 
		 	if(!jQuery.isFunction(arrCrosSlide[crossSlide] )) {
				jQuery("#" + crossSlide).height(jQuery("#" + crossSlide + ' .crosslide:first').height());
				jQuery("#" + crossSlide).width(jQuery("#" + crossSlide + ' .crosslide:first').width());
				jQuery("#" + crossSlide).hide();
				jQuery("#" + crossSlide).crossSlide({
				  sleep: 2,
				  fade: 1
				}, eval('[' + arrCrosSlide[crossSlide] + ']'));
				jQuery("#" + crossSlide).show();
			}
		}
	});
};