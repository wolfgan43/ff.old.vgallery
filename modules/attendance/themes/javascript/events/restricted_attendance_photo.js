jQuery(function() {
	jQuery("#photo .row").each(function() {
		/*var desc = jQuery(this).children(".description").html();
		var date = jQuery(this).children(".date").html();
		var office = jQuery(this).children(".office").html();
		var user = jQuery(this).children(".user").html();
		var event = jQuery(this).children(".event").html();
		var argument = jQuery(this).children(".argument").html();*/
		var linkImg = jQuery(this).find(".data > a:not('.add')").attr("href");
		/*if(jQuery(desc).text() == "") {
			desc = "";
		}*/
		if(jQuery(this).children(".office").is("span")) {
			if (jQuery(this).parent().find("H2." + jQuery(this).children(".office").find("A").text().replace(jQuery(this).children(".office").find("LABEL").text(), "").replace(/[^a-zA-Z0-9]+/g,'') ).html() === null )  {

				jQuery(this).before('<h2 class="'+ jQuery(this).children(".office").find("A").text().replace(jQuery(this).children(".office").find("LABEL").text(), "").replace(/[^a-zA-Z0-9]+/g,'') + '">'+ jQuery(this).children(".office").find("A").text().replace(jQuery(this).children(".office").find("LABEL").text(), "") + '</h2>')
				
			}
		}		
		
		jQuery(this).append(/*'<div class="more">' + desc +  date +  office +  user +  event +  argument + '</div>*/'<a class="download" href="javascript:void(window.open(\'' + linkImg + '\'))" ></a>');
	});
	
	
	
	
	/*jQuery("#photo .row").hover(function() {
		jQuery(this).children(".more").stop(true,true).animate({
			"top" : "+=210",
			opacity: 0.9
		}, 600);
	}, function() {
		jQuery(this).children(".more").stop(true,true).animate({
			"top" : "-=210",
			opacity: 0
		}, 600);
	});*/

	ff.injectCSS("jquery.fn.fancybox","/themes/library/plugins/jquery.fancybox/jquery.fancybox.css", function() {	
		ff.pluginLoad("jquery.fn.fancybox","/themes/library/plugins/jquery.fancybox/jquery.fancybox.js", function() {
			jQuery(".fancybox").fancybox({
				'transitionIn'	:	'fade',
					'transitionOut'	:	'fade',
					'titleShow'		: 	true ,
					'titlePosition'	: 	'over' ,
					'speedIn'		:	600, 
					'speedOut'		:	200, 
					'width'			: 	560, 
					'height'		: 	340,
					'padding'		: 	10, 
					'margin'		: 	20, 
					'autoScale'		: 	true, 
					'showCloseButton':  true, 
					'showNavArrows'	: 	true, 
					'centerOnScroll': 	true, 
					'opacity'		:	false,
					'modal'			: 	false

			
			});
		});
		
	});
	
});