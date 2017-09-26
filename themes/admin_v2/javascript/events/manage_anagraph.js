if(!jQuery("#Anagraph .row .actions").is("DIV")) {
	jQuery("#Anagraph .row ").each(function() {
		jQuery(this).find(".ffButton").wrapAll('<div class="actions"/>');
		
		jQuery(this).hover(function() {
			jQuery(this).children(".grid .actions").show();
		},function() {
			jQuery(this).children(".grid .actions").hide();
		});
	});
}