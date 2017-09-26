ff.cms.fn.owlcarousel = function(targetid) { 
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
	
	ff.pluginLoad("jquery.owlcarousel", "/themes/library/plugins/jquery.owlcarousel/jquery.owlcarousel.js", function() {
		jQuery(targetid + '.owlcarousel').closest("UL").owlCarousel({
			autoPlay: 3000,
			stopOnHover: true,
			items: 1,
			itemsCustom: false,
			itemsDesktop: [1199,2],
			itemsDesktopSmall: [980,2],
			itemsTablet: [768,2],
			itemsTabletSmall: false,
			itemsMobile: [479,1],
			itemsScaleUp: true,
			navigation: true,
			navigationText: [
				"<i class='glyphicon glyphicon-chevron-left'></i>",
				"<i class='glyphicon glyphicon-chevron-right'></i>"
			],
		});
	}, false);
};
