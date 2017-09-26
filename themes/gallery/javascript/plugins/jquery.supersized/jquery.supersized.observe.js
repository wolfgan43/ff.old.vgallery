var arrSupersizedImage = [];

ff.cms.fn.supersized = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	if(!arrSupersizedImage.length > 0 && jQuery(targetid + ".supersized").length > 0) {
		var template = '<div id="supersized-loader"></div><ul id="supersized"></ul>';

		template += /*Thumbnail Navigation */
			'<div id="prevthumb"></div>'
			+ '<div id="nextthumb"></div>';
		
		template += /*Thumb Tray */
			'<div id="thumb-tray" class="load-item">'
			+	'<div id="thumb-back"></div>'
			+	'<div id="thumb-forward"></div>'
			+ '</div>';
			
		template += /*Time Bar */
			'<div id="progress-back" class="load-item">'
			+	'<div id="progress-bar"></div>'
			+ '</div>';
			
			template += /*Control Bar Start */
			'<div id="controls-wrapper" class="load-item">'
			+	'<div id="controls">'
			+		'<a id="play-button"><img id="pauseplay" src="img/pause.png"/></a>';
				template += /*Slide counter */
					'<div id="slidecounter">'
					+	'<span class="slidenumber"></span> / <span class="totalslides"></span>'
					+ '</div>';
				template += /*Slide captions displayed here */
					'<div id="slidecaption"></div>';
					
				template += /*Thumb Tray button */
					'<a id="tray-button"><img id="tray-arrow" src="img/button-tray-up.png"/></a>';
					
				template += /*Navigation */
					'<ul id="slide-list"></ul>';
			template +=	/*Control Bar End */
				'</div>'
			+ '</div>';


		jQuery(targetid + ".supersized").closest("div").hide();
		jQuery(targetid + ".supersized").closest("div").before(template);
	
	
		jQuery(targetid + ".supersized").each(function() {
			if(jQuery(this).is("a")) {
				arrSupersizedImage.push({
						'image' : jQuery(this).attr("href")
						, 'title' : (jQuery(this).find("img").attr("title") == "" ? jQuery(this).find("img").attr("alt") : jQuery(this).find("img").attr("title"))
						, 'thumb' : jQuery(this).find("img").attr("src")
						, 'url' : ''
					});
			} else {
				arrSupersizedImage.push({
						'image' : jQuery(this).attr("src")
						, 'title' : (jQuery(this).attr("title") == "" ? jQuery(this).attr("alt") : jQuery(this).attr("title"))
						, 'thumb' : jQuery(this).attr("src")
						, 'url' : ''
					});
			}
		});
		jQuery(targetid + ".supersized").closest("div").remove();

		jQuery.supersized({
			/* Functionality    */
			slideshow               :   1,			/* Slideshow on/off */
			autoplay				:	1,			/* Slideshow starts playing automatically */
			start_slide             :   1,			/* Start slide (0 is random) */
			stop_loop				:	0,			/* Pauses slideshow on last slide */
			random					: 	0,			/* Randomize slide order (Ignores start slide) */
			slide_interval          :   3000,		/* Length between transitions */
			transition              :   6, 			/* 0-None, 1-Fade, 2-Slide Top, 3-Slide Right, 4-Slide Bottom, 5-Slide Left, 6-Carousel Right, 7-Carousel Left */
			transition_speed		:	1000,		/* Speed of transition */
			new_window				:	1,			/* Image links open in new window/tab */
			pause_hover             :   0,			/* Pause slideshow on hover */
			keyboard_nav            :   1,			/* Keyboard navigation on/off */
			performance				:	1,			/* 0-Normal, 1-Hybrid speed/quality, 2-Optimizes image quality, 3-Optimizes transition speed // (Only works for Firefox/IE, not Webkit) */
			image_protect			:	1,			/* Disables image dragging and right click with Javascript */
													   
			/* Size & Position	*/					   
			min_width		        :   0,			/* Min width allowed (in pixels) */
			min_height		        :   0,			/* Min height allowed (in pixels) */
			vertical_center         :   1,			/* Vertically center background */
			horizontal_center       :   1,			/* Horizontally center background */
			fit_always				:	0,			/* Image will never exceed browser width or height (Ignores min. dimensions) */
			fit_portrait         	:   1,			/* Portrait images will not exceed browser height */
			fit_landscape			:   0,			/* Landscape images will not exceed browser width */
													   
			/* Components		*/					
			slide_links				:	'blank',	/* Individual links for each slide (Options: false, 'number', 'name', 'blank') */
			thumb_links				:	1,			/* Individual thumb links for each slide */
			thumbnail_navigation    :   0,			/* Thumbnail navigation */
			slides 					:  	arrSupersizedImage,	/* Slideshow Images */
										
										
										
			/* Theme Options	*/		   
			progress_bar			:	1,			/* Timer for each slide	*/						
			mouse_scrub				:	0
			
		});
	}
};