ff.cms.fn.audioplayer = function(targetid) {
    var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

    /*css*/
    ff.pluginLoad("swfobject", "/themes/library/swfobject/swfobject.js", function () {
        jQuery(targetid + ".audioplayer").each(function(){
            var actualMp3 = jQuery(this).attr("href");
            var actualID = jQuery(this).attr("id");
            if(actualID === undefined || !actualID.length > 0)
            	actualID = jQuery(this).parent().attr("id");

            jQuery(this).replaceWith('<div id="' + actualID + '"></div>');
            
			/* JAVASCRIPT VARS
			// cache buster */
			var cacheBuster = "?t=" + Date.parse(new Date());		
			
			/* stage dimensions */
			var stageW = "32";/*"100%"; */
			var stageH = "32";/*"100%"; */
			
			/* PARAMS */
			var params = {};
			params.wmode = "transparent";
			params.quality = "high";
			params.allowScriptAccess = "always";			

		    /* FLASH VARS */
			var flashvars = {};				
			
			flashvars.playerid = actualID;
			flashvars.file = actualMp3;
			/* other vars */
			flashvars.auto="no"; /* yes OR no */
			flashvars.sendstop="yes"; /* yes OR no */
			flashvars.repeat="1"; /*times (0 - infinite loop) */
			flashvars.buttondir="/themes/library/plugins/swfobject.audioplayer/buttons/classic"; /* classic OR classic_small OR negative OR negative_small */

			flashvars.bgcolor="0x000000"; /* 0x000000 OR 0xffffff */
			flashvars.mode="playpause"; /* "play/pause" OR "play/stop" */
			/** EMBED THE SWF**/
			swfobject.embedSWF("/themes/library/plugins/swfobject.audioplayer/audioplay.swf" + cacheBuster, actualID, stageW, stageH, "9.0.124", "/themes/library/plugins/swfobject.audioplayer/audioplay.swf", flashvars, params);
        });
    }, false);  
};