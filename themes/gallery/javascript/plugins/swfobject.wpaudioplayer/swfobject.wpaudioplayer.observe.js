ff.cms.fn.wpaudioplayer = function(targetid) {
    var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

    /*css*/
    ff.pluginLoad("swfobject", "/themes/library/swfobject/swfobject.js", function () {
        jQuery(targetid + ".wpaudioplayer").each(function(){
            var actualMp3 = jQuery(this).attr("href");
            var actualID = jQuery(this).attr("id");
            if(actualID === undefined || !actualID.length > 0)
            	actualID = jQuery(this).parent().attr("id");

            jQuery(this).replaceWith('<div id="' + actualID + '"></div>');
            
			/* JAVASCRIPT VARS
			// cache buster*/
			var cacheBuster = "?t=" + Date.parse(new Date());		
			
			/* stage dimensions*/
			var stageW = "290";/*"100%";*/
			var stageH = "32";/*"100%";*/
			
			/* PARAMS*/
			var params = {};
			params.wmode = "transparent";
			params.quality = "high";
			params.allowScriptAccess = "always";			

		    /* FLASH VARS */
			var flashvars = {};				
			
			flashvars.playerID = actualID;
			flashvars.soundFile = actualMp3;
			
			flashvars.artists = "";
			flashvars.titles = actualMp3.substr(actualMp3.lastIndexOf("/") + 1, (actualMp3.lastIndexOf(".") - actualMp3.lastIndexOf("/")));
			
			/* other vars*/
			flashvars.autostart="no"; /* yes OR no. 	if yes, player starts automatically*/
			flashvars.loop="no"; /* yes OR no. 	if yes, player loops*/
			flashvars.animation="no"; /* yes OR no. if no, player is always open */
			flashvars.remaining="no"; /* yes OR no. if yes, shows remaining track time rather than ellapsed time */
			flashvars.noinfo="no"; /* yes OR no. 	if yes, disables the track information display */
			flashvars.noinfo="60"; /*	initial volume level (from 0 to 100) */
			flashvars.buffer="5";/*buffering time in seconds */
			flashvars.encode="no";/*	indicates that the mp3 file urls are encoded */
			flashvars.checkpolicy="no";/*	tells Flash to look for a policy file when loading mp3 files (this allows Flash to read ID3 tags from files hosted on a different domain) */
			flashvars.rtl="no";/*	switches the layout to RTL (right to left) for Hebrew and Arabic languages */
			
			flashvars.width=stageW; /* 	width of the player. e.g. 290 (290 pixels) or 100% */
			flashvars.transparentpagebg="no"; /*if yes, the player background is transparent (matches the page background) */
			flashvars.pagebg="NA"; /*player background color (set it to your page background when transparentbg is set to �no�) */
			
			flashvars.bg="E5E5E5"; /*	Background */
			flashvars.leftbg="CCCCCC"; /*	Speaker icon/Volume control background */
			flashvars.lefticon="333333"; /*	Speaker icon */
			flashvars.voltrack="F2F2F2"; /*	Volume track */
			flashvars.volslider="666666"; /*	Volume slider */
			flashvars.rightbg="B4B4B4"; /*	Play/Pause button background */
			flashvars.rightbghover="999999"; /*	Play/Pause button background (hover state) */
			flashvars.righticon="333333"; /*	Play/Pause icon */
			flashvars.righticonhover="FFFFFF"; /*	Play/Pause icon (hover state) */
			flashvars.loader="009900"; /*	Loading bar */
			flashvars.track="FFFFFF"; /*	Loading/Progress bar track backgrounds */
			flashvars.tracker="DDDDDD"; /*	Progress track */
			flashvars.border="CCCCCC"; /*	Progress bar border */
			flashvars.skip="666666"; /*	Previous/Next skip buttons */
			flashvars.text="333333"; /*	Text */
			/** EMBED THE SWF**/
			swfobject.embedSWF("/themes/library/plugins/swfobject.wpaudioplayer/player.swf" + cacheBuster, actualID, stageW, stageH, "9.0.124", "/themes/library/plugins/swfobject.wpaudioplayer/player.swf", flashvars, params);
        });
    }, false);  
};