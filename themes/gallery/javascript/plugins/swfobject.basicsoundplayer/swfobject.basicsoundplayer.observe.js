ff.cms.fn.basicsoundplayer = function(targetid) {
    var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

    /*css*/
    ff.pluginLoad("swfobject", "/themes/library/swfobject/swfobject.js", function () {
        jQuery(targetid + ".basicsoundplayer").each(function(){
            var actualMp3 = jQuery(this).attr("href");
            var actualID = jQuery(this).attr("id");
            if(actualID === undefined || !actualID.length > 0)
            	actualID = jQuery(this).parent().attr("id");

            jQuery(this).replaceWith('<div id="' + actualID + '"></div>');
            
			/* JAVASCRIPT VARS
			// cache buster*/
			var cacheBuster = "?t=" + Date.parse(new Date());		
			
			/* stage dimensions*/
			var stageW = "50";/*"100%";*/
			var stageH = "50";/*"100%";*/
			
			
			/* ATTRIBUTES*/
		    var attributes = {};
		    attributes.id = actualID;
		    attributes.name = attributes.id;
		    
			/* PARAMS*/
			var params = {};
			params.wmode = "transparent";
			params.allowfullscreen = "true";
			params.allowScriptAccess = "always";			
			params.bgcolor = "#ffffff";
			

		    /* FLASH VARS */
			var flashvars = {};				
			
			/*/ if commented / delete these lines, the component will take the stage dimensions defined 
			/// above in "JAVASCRIPT SECTIONS" section or those defined in the settings xml		*/	
			flashvars.componentWidth = stageW;
			flashvars.componentHeight = stageH;
			
			/*/ path to the content folder(where the xml files, images or video are nested)
			/// if you want to use absolute paths(like "http://domain.com/images/....") then leave it empty("") */
			flashvars.pathToFiles = "";
			flashvars.xmlPath = "/themes/library/plugins/swfobject.basicsoundplayer/player/xml/settings.xml";
			
			/* other vars */
			flashvars.artistName = "";
			flashvars.songName = actualMp3.substr(actualMp3.lastIndexOf("/") + 1, (actualMp3.lastIndexOf(".") - actualMp3.lastIndexOf("/")));
			flashvars.songURL = actualMp3;
						
			
			/** EMBED THE SWF**/
			swfobject.embedSWF("/themes/library/plugins/swfobject.basicsoundplayer/preview.swf" + cacheBuster, attributes.id, stageW, stageH, "9.0.124", "/themes/library/plugins/swfobject.basicsoundplayer/js/expressInstall.swf", flashvars, params);
        });
    }, false);  
};