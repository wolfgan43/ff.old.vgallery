ff.cms.fn.pregiogallery = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
		
	jQuery(targetid + ".pregiogallery").parent().parent().parent().wrap('<div class="gallerycontainer" />');
	jQuery(targetid + ".pregiogallery").parent().parent().parent().after('<div class="galleryviewer"></div>');
	jQuery(".galleryviewer").html('<a href="' + jQuery(".gallery_image:first").children("a").attr("href") + '" class="imglink"><img src="' + jQuery(".gallery_image:first").children("a").attr("href") + '"/></a>').hide().fadeIn();
	
		jQuery(".gallery_image").children("a").each(function(){
			jQuery(this).click(function() {
				var imgsrc = jQuery(this).attr("href");
				jQuery(".galleryviewer .imglink img").attr('src', imgsrc).hide().fadeIn();
				jQuery(".galleryviewer .imglink").attr('href', imgsrc);
							
				return false;
			});
		});
		jQuery(".imglink").hover(
			function() {
				jQuery(this).addClass("imghover");
			}, function() {
				jQuery(this).removeClass("imghover");
		});
		ff.pluginLoad("jquery.prettyPhoto", "/themes/library/plugins/jquery.prettyphoto/jquery.prettyphoto.js", function() {	
			jQuery(".imglink").prettyPhoto({
				opacity: 0.60, /* Value between 0 and 1 */
				allow_resize: true, /* Resize the photos bigger than viewport. true/false */
				default_width: 500,
				default_height: 344
			});		
		}, true);
		
};
