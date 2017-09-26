ff.cms.fn.dewplayer = function(targetid) {
    var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

    //css
    ff.pluginLoad("swfobject", "/themes/library/swfobject/swfobject.js", function () {
        jQuery(targetid + ".dewplayer").each(function(){
            var actualMp3 = jQuery(this).attr("href");
            var actualID = jQuery(this).attr("id");
            if(actualID === undefined || !actualID.length > 0)
            	actualID = jQuery(this).parent().attr("id");

            var playerList = "dewplayer";

            jQuery(this).replaceWith('<div id="' + actualID + '"></div>');
            
            var flashvars = {
              mp3: actualMp3
            };
            var params = {
              wmode: "transparent"
            };
            var attributes = {
              id: playerList
            };

            swfobject.embedSWF("/themes/library/plugins/swfobject.dewplayer/" + playerList + ".swf", actualID, "200", "20", "9.0.0", false, flashvars, params, attributes);
        });
        jQuery(targetid + ".dewplayer-mini").each(function(){
            var actualMp3 = jQuery(this).attr("href");
            var actualID = jQuery(this).attr("id");
            if(actualID === undefined || !actualID.length > 0)
            	actualID = jQuery(this).parent().attr("id");

            var playerList = "dewplayer-mini";

            jQuery(this).replaceWith('<div id="' + actualID + '"></div>');
            
            var flashvars = {
              mp3: actualMp3
            };
            var params = {
              wmode: "transparent"
            };
            var attributes = {
              id: playerList
            };

            swfobject.embedSWF("/themes/library/plugins/swfobject.dewplayer/" + playerList + ".swf", actualID, "160", "20", "9.0.0", false, flashvars, params, attributes);
        });
        jQuery(targetid + ".dewplayer-multi").each(function(){
            var actualMp3 = jQuery(this).attr("href");
            var actualID = jQuery(this).attr("id");
            if(actualID === undefined || !actualID.length > 0)
            	actualID = jQuery(this).parent().attr("id");

            var playerList = "dewplayer-multi";

            jQuery(this).replaceWith('<div id="' + actualID + '"></div>');
            
            var flashvars = {
              mp3: actualMp3
            };
            var params = {
              wmode: "transparent"
            };
            var attributes = {
              id: playerList
            };

            swfobject.embedSWF("/themes/library/plugins/swfobject.dewplayer/" + playerList + ".swf", actualID, "240", "20", "9.0.0", false, flashvars, params, attributes);
        });
        jQuery(targetid + ".dewplayer-rect").each(function(){
            var actualMp3 = jQuery(this).attr("href");
            var actualID = jQuery(this).attr("id");
            if(actualID === undefined || !actualID.length > 0)
            	actualID = jQuery(this).parent().attr("id");

            var playerList = "dewplayer-rect";

            jQuery(this).replaceWith('<div id="' + actualID + '"></div>');
            
            var flashvars = {
              mp3: actualMp3
            };
            var params = {
              wmode: "transparent"
            };
            var attributes = {
              id: playerList
            };

            swfobject.embedSWF("/themes/library/plugins/swfobject.dewplayer/" + playerList + ".swf", actualID, "240", "20", "9.0.0", false, flashvars, params, attributes);
        });
        jQuery(targetid + ".dewplayer-bubble").each(function(){
            var actualMp3 = jQuery(this).attr("href");
            var actualID = jQuery(this).attr("id");
            if(actualID === undefined || !actualID.length > 0)
            	actualID = jQuery(this).parent().attr("id");

            var playerList = "dewplayer-bubble";

            jQuery(this).replaceWith('<div id="' + actualID + '"></div>');
            
            var flashvars = {
              mp3: actualMp3
            };
            var params = {
              wmode: "transparent"
            };
            var attributes = {
              id: playerList
            };
 
            swfobject.embedSWF("/themes/library/plugins/swfobject.dewplayer/" + playerList + ".swf", actualID, "250", "65", "9.0.0", false, flashvars, params, attributes);
        });
        jQuery(targetid + ".dewplayer-playlist").each(function(){
            var actualXml = jQuery(this).attr("href");
            var actualID = jQuery(this).attr("id");
            if(actualID === undefined || !actualID.length > 0)
            	actualID = jQuery(this).parent().attr("id");

            var playerList = "dewplayer-playlist";

            jQuery(this).replaceWith('<div id="' + actualID + '"></div>');
            
            var flashvars = {
              xml: actualXml
            };
            var params = {
              wmode: "transparent"
            };
            var attributes = {
              id: playerList
            };
 
            swfobject.embedSWF("/themes/library/plugins/swfobject.dewplayer/" + playerList + ".swf", actualID, "240", "200", "9.0.0", false, flashvars, params, attributes);
        });
        jQuery(targetid + ".dewplayer-playlistcover").each(function(){
            var actualXml = jQuery(this).attr("href");
            var actualID = jQuery(this).attr("id");
            if(actualID === undefined || !actualID.length > 0)
            	actualID = jQuery(this).parent().attr("id");

            var playerList = "dewplayer-playlist-cover";

            jQuery(this).replaceWith('<div id="' + actualID + '"></div>');
            
            var flashvars = {
              xml: actualXml
            };
            var params = {
              wmode: "transparent"
            };
            var attributes = {
              id: playerList
            };
 
            swfobject.embedSWF("/themes/library/plugins/swfobject.dewplayer/" + playerList + ".swf", actualID, "240", "200", "9.0.0", false, flashvars, params, attributes);
        });
    }, false);  
};