ff.cms.fn.middlegallery = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	var imgContainer = {};
	var selected;
	var switching = false
	var previewObj = jQuery(targetid + ".middlegallery").closest(".block:not(.gal)").find("IMG:first");
	
	var changeCurrentImage = function(event){
		if(!switching){
			var proposedCurrent = this;
			
			if (selected != proposedCurrent) {
				var previewObj = jQuery(proposedCurrent).closest(".block:not(.gal)").find("IMG:first"); 
				switching = true;
				jQuery(selected).removeClass("focused");
				jQuery(proposedCurrent).addClass("focused");

				var filename = jQuery(proposedCurrent).find("img").attr("alt");

				if(jQuery(proposedCurrent).is("a")) {
					jQuery(previewObj).parent().attr("href", jQuery(proposedCurrent).attr("href")); 
				} else {
					jQuery(previewObj).parent().attr("href", imgContainer[filename].src); 
				}

				/*if(0 && jQuery(previewObj).attr("src").indexOf('/thumb') >= 0) {
		            jQuery(previewObj).parent().attr("href", imgContainer[filename].src.replace("/thumb", "/viewer")); 
				} else {
					jQuery(previewObj).parent().attr("href", imgContainer[filename].src); //.replace("/thumb", "/viewer")); 
				}*/

	           /* jQuery(previewObj).parent().attr("rel", jQuery(previewObj).parent().attr("class") + "");*/
	            
	            jQuery(previewObj).fadeOut("fast", function(){
				 	switching = false; 
					jQuery(this).replaceWith(imgContainer[filename]);
					jQuery(this).hide().fadeIn("slow", function(){});
				
				});
	           /* if(jQuery(targetid + "a.middlegallery").length > 0) {
	                jQuery(targetid + "a.middlegallery img").each(function() {
	                    if(jQuery(this).attr("alt") !=  imgContainer[filename].alt) {
	                        jQuery(this).parent().attr("rel", jQuery("IMG.preview").parent().attr("class") + "");    
	                    } else {
	                        jQuery(this).parent().removeAttr("rel");
	                        
	                    }
	                });
	            }*/

				selected = proposedCurrent;
			}
		}
		if(event.type == "click")
			return false;
	}

	var makeImageClickable = function(event){
		var l = jQuery(".middlegallery");
	/*	l.hover(changeCurrentImage); */
		l.unbind('click');
		l.click(changeCurrentImage);
		/*l.removeClass("middlegallery");*/
	}

	var preloadImages = function() {
		jQuery(targetid + ".middlegallery").each(function(i){
		    var link = jQuery(this).attr("href");
		    var thumb = jQuery(this).children(":first").attr("src");
		    var alt = jQuery(this).children(":first").attr("alt");
		    
		    var preview = jQuery(previewObj).attr("src");
			var new_preview = link;
			if(jQuery(previewObj).attr("class")) { 
				new_preview = new_preview.replace("/cm/showfiles.php", "/cm/showfiles.php/" + jQuery(previewObj).attr("class"));
			}

			/*
			if(jQuery(previewObj).attr("src").indexOf('/thumb') >= 0) {
			    var new_preview = link.replace("/viewer", "/thumb") + preview.substring(preview.indexOf("?"));
	            new_preview = new_preview.replace("/cm/showfiles.php", "/thumb");
			} else {
			}*/
			var filename = alt;

			if (!imgContainer[filename]){
				imgContainer[filename] = new Image();
				/*	jQuery(imgContainer[filename]).load(function(){*/
				imgContainer[filename].src = new_preview;
				imgContainer[filename].alt = alt;
				
				imgContainer[filename].className = jQuery(previewObj).attr("class");
			}
		});
		makeImageClickable();
	}
	if(jQuery(targetid + ".middlegallery").length > 0) {
	    if(jQuery(previewObj).attr("src") == undefined) {
		    console.error("Need 'extra settings' on vgallery detail"); 
	    } else {
		    preloadImages();
		    /* Outline per la preview corrente.*/
		    selected = jQuery(previewObj).attr("alt");
	    }    
    }
};