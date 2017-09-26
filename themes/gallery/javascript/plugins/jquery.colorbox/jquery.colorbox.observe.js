ff.cms.fn.colorbox = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	if(jQuery(targetid + ".colorbox").length > 0) {
		ff.load("jquery.plugins.colorbox", function() {	
			jQuery(targetid + "a.colorbox").attr("rel", "colorbox[gallery]");
				if(jQuery("a.middlegallery").length > 0) {
		        jQuery("a.middlegallery img").each(function() {
	            	jQuery(this).parent().attr("rel", "colorbox[gallery]"); 
					});
				}
				jQuery("a[rel^='colorbox[gallery]']").colorbox({
					transition: "fade",
					speed: 300,
					fadeOut: 300,
					width: false,
					initialWidth: "600",
					innerWidth: false,
					maxWidth: false,
					height: false,
					initialHeight: "450",
					innerHeight: false,
					maxHeight: false,
					scalePhotos: true,
					scrolling: true,
					inline: false,
					html: false,
					iframe: false,
					fastIframe: true,
					photo: false,
					href: false,
					title: false,
					rel: false,
					opacity: 0.9,
					preloading: true,
					className: false,

					/* alternate image paths for high-res displays */
					retinaImage: false,
					retinaUrl: false,
					retinaSuffix: '@2x.$1',

					/* internationalization */
					current: "image {current} of {total}",
					previous: "previous",
					next: "next",
					close: "close",
					xhrError: "This content failed to load.",
					imgError: "This image failed to load.",

					open: false,
					returnFocus: true,
					reposition: true,
					loop: true,
					slideshow: false,
					slideshowAuto: true,
					slideshowSpeed: 2500,
					slideshowStart: "start slideshow",
					slideshowStop: "stop slideshow",
					photoRegex: /\.(gif|png|jp(e|g|eg)|bmp|ico|webp)((#|\?).*)?$/i,

					onOpen: false,
					onLoad: false,
					onComplete: false,
					onCleanup: false,
					onClosed: false,
					overlayClose: true,
					escKey: true,
					arrowKey: true,
					top: false,
					bottom: false,
					left: false,
					right: false,
					fixed: false,
					data: undefined
				});
		});
	}
};