ff.cms.fn.youtube = function(targetid) {
    ff.injectCSS("jqueryyoutube", "/themes/library/jquery.youtube/jquery.youtube.css", function() {
        var targetid = targetid;
        if(targetid.length > 0)
                targetid = targetid + " ";

        var videoWidth = "400";
        var videoHeight = "200";
        var imgPath = 'http://img.youtube.com/vi';
        var imgSize = {
                "default" : "default.jpg"
                , "large" : "hqdefault.jpg"
                , "medium" : "mqdefault.jpg"
                , "small" : "sddefault.jpg"
                , "huge" : "maxresdefault.jpg"
        }; 

        var selImgSize = "default";
        var youtubeKey = "";
        var showVideoImage = true;
        var showVideoTitle = false;
        var showVideoDescription = false;
        var limitCharDesc = 140;
        var charlimitator = "(...)"; 

        if(jQuery(targetid + "a.youtube").length > 0) {
            var youTubeContainer = (targetid ? targetid : "body");
            var youTubePlayerId = (targetid ? targetid.replace(/\W/g, '')+ '-' : '') + 'youtube-content';
            if(!jQuery(youTubeContainer + " .youtube-container").length) {
                jQuery(youTubeContainer).prepend('<div class="youtube-container"><div class="player-container" id="' + youTubePlayerId + '"></div></div>');
            }

            jQuery(targetid + "a.youtube").each(function() {
                var youtubevideo = jQuery(this).attr("href").replace("#", "");
                var youtubevideo_id = youtube_parser(youtubevideo);

                if(youtubevideo_id) {
                    jQuery(this).addClass("youtube-parsed").attr("rel", youtubevideo_id).removeAttr("href");
                    if(showVideoImage) {
                        jQuery(this).html('<img src="' + imgPath + "/" + youtubevideo_id + "/" + imgSize[selImgSize] + '" />');	
                    }

                    var elem = jQuery(this);
                    if(youtubeKey.length && (showVideoTitle || showVideoDescription)) {
                        $.get("https://www.googleapis.com/youtube/v3/videos?id=" + youtubevideo_id + "&key=" + youtubeKey + "&part=snippet,contentDetails,statistics,status", function(data){ 
                            if (data["items"].length) {
                                if(showVideoTitle) {
                                    var titoloVideo = data["items"]["0"]["snippet"]["localized"]["title"];
                                    elem.append("<div class='youtube-title'>" + titoloVideo + "</div>");
                                }
                                if(showVideoDescription) {
                                    var descrVideo = data["items"]["0"]["snippet"]["localized"]["description"];
                                    if(descrVideo.length > limitCharDesc)
                                        descrVideo = descrVideo.substring(0, limitCharDesc) + charlimitator;
                                    elem.after("<div class='youtube-description'>" + descrVideo + "</div>");
                                }
                            }
                        });
                    }
                }
            }); 
            if(jQuery(targetid + "a.youtube-parsed").length) {
                var newVideoWidth, newVideoHeight;
                jQuery(targetid + "a.youtube-parsed").click(function() {
                    var windowWidth = $(window).width();
                    var styleText;
                    if(windowWidth < videoWidth) {
                        newVideoWidth = "100%";
                        if(windowWidth < 1023){
                            newVideoHeight = "268px !important";
                            styleText = "left:0;top:50%;margin-top:-134px;";
                        } else if(windowWidth < 767) {
                            newVideoHeight = "175px !important";
                            styleText = "left:0;top:50%;margin-top:-86px;";
                        }
                    } else {
                        newVideoWidth = videoWidth + "px";
                        newVideoHeight = videoHeight + "px";
                        styleText = "top:50%;left:50%;margin-top:-" + parseInt(videoHeight/2) + "px;margin-left:-" + parseInt(videoWidth/2) + "px;";
                    }
                    jQuery(".youtube-container .player-container").html('<iframe src="https://www.youtube.com/embed/' +jQuery(this).attr("rel") + '?autoplay=1&" frameborder="0" allowfullscreen></iframe>');
                    jQuery(".youtube-container").show();

                    jQuery(".youtube-container").bind("click", function(e) {
                        closeVideo();
                    });
                    jQuery(document).bind("keyup", function(e) {
                        closeVideo(e);
                    });
                });
            }
        }
    });
};

function youtube_parser(url){
    var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
    var match = url.match(regExp);
    if (match&&match[7].length==11){
        return match[7];
    }else{
        return "";
    }
}

function closeVideo(e) {
    if(e == undefined || e.keyCode == 27) {
        jQuery(".youtube-container .player-container").html("");
        jQuery(".youtube-container").hide();
        jQuery(document).unbind("keyup");
    }
}