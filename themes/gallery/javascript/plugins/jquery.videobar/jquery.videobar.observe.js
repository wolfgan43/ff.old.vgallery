ff.cms.fn.videobar = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	/*css*/
	jQuery(targetid + "a.videobar").each(function() {
		var channel = jQuery(this).attr("href").replace("#", "");
		var videobar_elem = jQuery(this).attr("id");
		ff.pluginLoad("google.search", "http://www.google.com/uds/api?file=uds.js&v=1.0&source=uds-vbw", function() {	
			ff.pluginLoad("google.search.videobar", "http://www.google.com/uds/solutions/videobar/gsvideobar.js?mode=new", function() {	
				var videoBar;
				var options = {
				    largeResultSet : false,
				    horizontal : true,
				    autoExecuteList : {
				      cycleTime : GSvideoBar.CYCLE_TIME_MEDIUM,
				      cycleMode : GSvideoBar.CYCLE_MODE_LINEAR,
				      executeList : ["ytchannel:" + channel] /*"simplesearch",ytchannel:fordmodels","ytfeed:most_viewed.this_week","ytfeed:top_rated.this_week","ytfeed:google_news","ytfeed:recently_featured","ytchannel:fordmodels"*/
				    }
				  }

				videoBar = new GSvideoBar(document.getElementById(videobar_elem),
				                          GSvideoBar.PLAYER_ROOT_FLOATING,
				                          options);
			});
		});
	});
};