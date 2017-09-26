jQuery(function() {
	jQuery("#Anagraph .row ").each(function() {
		jQuery(this).find(".ffButton").wrapAll('<div class="actions"/>');
		jQuery(this).find(".actions").hide();

		jQuery(this).hover(function() {
			jQuery(this).children(".actions").show();
		},function() {
			jQuery(this).children(".actions").hide();
		});
	});
});