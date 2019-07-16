	jQuery(".check-useragent input").click(function() {
		jQuery(this).closest(".row").next().fadeToggle();
	});

	jQuery(".check-useragent input").each(function() {
		if(jQuery(this).is(":checked")) {
			jQuery(this).closest(".row").next().show();
		} else {
			jQuery(this).closest(".row").next().hide();
		}
	});
