ff.cms.fn.contentflow = function(targetid) { 
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
		
	if(jQuery(targetid + ".contentflow").parent().parent().attr("id") !== undefined) {
		var containerID = jQuery(targetid + ".contentflow").parent().parent().attr("id");
		var ContentFlowID = containerID  + '_contentflow';

		jQuery(targetid + ".contentflow").parent().parent().wrap('<div id="' + ContentFlowID + '" />');
		jQuery(targetid + ".contentflow").parent().parent().addClass("flow");
		jQuery(targetid + ".contentflow").parent().addClass("item");
		jQuery(targetid + ".contentflow").parent().hide();	
		
		if(jQuery(targetid + ".contentflow").is("a")) {
			jQuery(targetid + ".contentflow img").each(function() {
				jQuery(this).addClass("content");
				jQuery(this).attr("href", jQuery(this).parent().attr("href"));
				jQuery(this).unwrap();
			});
		} else {
			jQuery(targetid + ".contentflow").addClass("content").removeClass("contentflow");
		}	
		
		jQuery("#" + ContentFlowID + ' .item:not(:has(.content))').prepend('<img src="' + ff.site_path + '/themes/gallery/images/spacer.gif" class="content" />');

		jQuery("#" + ContentFlowID + ' .vgallery_description').addClass("caption").removeClass("contentflow");		
		
		jQuery("#" + ContentFlowID).prepend('<div class="loadIndicator"><div class="indicator"></div></div>');		
		jQuery("#" + ContentFlowID).append('<div class="globalCaption"></div><div class="scrollbar"><div class="slider"><div class="position"></div></div></div>');

		ff.pluginLoad("ContentFlow", "/themes/library/plugins/jquery.contentflow/contentflow.js", function() { 

		var myNewFlow = new ContentFlow(ContentFlowID
											, { 
												useAddOns: 'all'
												, loadingTimeout: 30000
												, circularFlow: true
												, verticalFlow: false
												, visibleItems: 3 
												, endOpacity: 1
												, startItem: 'center'
												, scrollInFrom: 'pre'
											}
											
										);
		}, false);
	}
};
