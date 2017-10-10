function LoadVideoBar(id, resultSet, mode, executeList) {
	var videoBar;
	var options = {
	    largeResultSet : resultSet,
	    horizontal : mode,
	    autoExecuteList : {
	      cycleTime : GSvideoBar.CYCLE_TIME_MEDIUM,
	      cycleMode : GSvideoBar.CYCLE_MODE_LINEAR,
	      executeList : [executeList] //"simplesearch",ytchannel:fordmodels","ytfeed:most_viewed.this_week","ytfeed:top_rated.this_week","ytfeed:google_news","ytfeed:recently_featured","ytchannel:fordmodels"
	    }
	  }

	videoBar = new GSvideoBar(document.getElementById(id),
	                          GSvideoBar.PLAYER_ROOT_FLOATING,
	                          options);
}