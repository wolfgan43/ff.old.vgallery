jQuery(function() {
	if(jQuery(".left-categories").length > 0) {
		jQuery(".grid").css({
			"float": "left"
			,"width": "782px"
		});
		jQuery("table.ffGrid").css({
			"width": "802px"
			,"margin-left": "-10px"
		});
		jQuery("table.ffGrid td:last-child").css({
			"width": "48px"
		}).children("a").css({
			"float":"left"
		});
		
	}
	
	/*if(ff.page_path.indexOf("/customer") >= 0 ){
		jQuery(".anagraph-content .data").each(function(){
			if(jQuery.trim(jQuery(this).text()) == "" ) {
				jQuery(this).remove();
			}
		});
		jQuery("#Anagraph .row").each(function() {
			jQuery(this).hover(function(){
				jQuery(this).children(".anagraph-content").fadeIn();
			}, function() {
				jQuery(this).children(".anagraph-content").hide();
			});
		});
	}*/
});