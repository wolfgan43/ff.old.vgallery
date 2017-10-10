ff.cms.fn.cloudzoom = function(targetid) { 
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
	
	if(	jQuery(targetid + '.cloudzoom').parent() !== undefined ) { 
		ff.pluginLoad("jquery.fn.CloudZoom", "/themes/library/plugins/jquery.cloudzoom/jquery.cloudzoom.js", function() {
			jQuery(targetid + '.cloudzoom').each(function() {
				jQuery(this).attr("class","cloud-zoom-gallery");
				jQuery(this).attr("rel", "useZoom: '" + jQuery(this).parent().attr("id") + "_zoom', smallImage: '" + jQuery(this).attr("href") + "'");
			});
		
			jQuery(targetid + ".cloud-zoom-gallery:first").before('<a href="' + jQuery(targetid + ".cloud-zoom-gallery:first").attr("href") + '" class="cloud-zoom" id="' + jQuery(targetid + ".cloud-zoom-gallery:first").parent().attr("id") + '_zoom">' + jQuery(targetid + ".cloud-zoom-gallery:first").html() + '</a>');
			jQuery("#" + jQuery(targetid + ".cloud-zoom-gallery:first").parent().attr("id") + '_zoom').children("img").width(100);
			jQuery("#" + jQuery(targetid + ".cloud-zoom-gallery:first").parent().attr("id") + '_zoom').children("img").height(100);
		
			jQuery('.cloud-zoom, .cloud-zoom-gallery').CloudZoom();
	    
		}, true);
	}	
};