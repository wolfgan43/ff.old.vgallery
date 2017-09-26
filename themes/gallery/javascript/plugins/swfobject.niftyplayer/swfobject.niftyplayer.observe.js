ff.cms.fn.niftyplayer = function(targetid) {
    var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

    /*css */
    ff.pluginLoad("swfobject", "/themes/library/swfobject/swfobject.js", function () {
        jQuery(targetid + ".niftyplayer").each(function(){
            var actualMp3 = jQuery(this).attr("href");
            var actualID = jQuery(this).attr("id");
            if(actualID === undefined || !actualID.length > 0)
            	actualID = jQuery(this).parent().attr("id");

            jQuery(this).replaceWith('<div id="' + actualID + '"></div>');
            
			/* JAVASCRIPT VARS
			// cache buster */
			var cacheBuster = "?t=" + Date.parse(new Date());		
			
			/* stage dimensions */
			var stageW = "165";/*"100%";*/
			var stageH = "38";/*"100%";*/
			
			/* PARAMS */
			var params = {};
			params.wmode = "transparent";
			params.quality = "high";
			params.allowScriptAccess = "always";			

		    /* FLASH VARS */
			var flashvars = {};				
			
			flashvars.playerID = actualID;

			flashvars.file = actualMp3;
            flashvars.as = "0";
			
			/** EMBED THE SWF**/
			swfobject.embedSWF("/themes/library/plugins/swfobject.niftyplayer/niftyplayer.swf" + cacheBuster, actualID, stageW, stageH, "9.0.124", "/themes/library/plugins/swfobject.niftyplayer/niftyplayer.swf", flashvars, params);
        });
    }, false);  
};