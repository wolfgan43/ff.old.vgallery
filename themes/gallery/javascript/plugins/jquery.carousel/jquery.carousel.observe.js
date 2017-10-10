ff.cms.fn.carousel = function(targetid) { 
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
	
	ff.pluginLoad("jquery.fn.mousewheel", "/themes/library/plugins/jquery.mousewheel/jquery.mousewheel.js", function() {
		ff.pluginLoad("jquery.carousel", "/themes/library/plugins/jquery.carousel/jquery.carousel.js", function() {
			if(jQuery(targetid + '.carousel').is("IMG")) {
				jQuery(targetid + '.carousel').addClass("cloudcarousel");
			} else {
				jQuery(targetid + '.carousel img').addClass("cloudcarousel");
			}
			jQuery(targetid + '.carousel').closest("DIV").append('<div id="carousel-title-box"></div>');
			jQuery(targetid + '.carousel').closest("DIV").append('<div id="carousel-alt-box"></div>');
			jQuery(targetid + '.carousel').closest("DIV").append('<div id="carousel-prev-box"></div>');
			jQuery(targetid + '.carousel').closest("DIV").append('<div id="carousel-next-box"></div>');
			jQuery(targetid + '.carousel').closest("DIV").CloudCarousel({	
				reflHeight: 56,
				reflGap:2,
				titleBox: jQuery('#carousel-title-box'),
				altBox: jQuery('#carousel-alt-box'),
				buttonLeft: jQuery('#carousel-prev-box'),
				buttonRight: jQuery('#carousel-next3-box'),
				yRadius:40,
				xPos: 285,
				yPos: 120,
				speed:0.15,
				mouseWheel:true
			});
		}, false);
	}, false);
};
