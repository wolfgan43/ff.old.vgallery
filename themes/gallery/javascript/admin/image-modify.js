var titleFollowDim = false;
jQuery(function() {
	var thumbTitle = jQuery("#ExtrasImageModify_dim_x").val() + "x" + jQuery("#ExtrasImageModify_dim_y").val();
	if(thumbTitle == "0x0")
		thumbTitle = "";
				
	if(thumbTitle == jQuery("#ExtrasImageModify_display_name").val())
		titleFollowDim = true;

	jQuery("#ExtrasImageModify_dim_x, #ExtrasImageModify_dim_y").keyup(function(event) {
		jQuery(this).val(isNaN(parseInt(jQuery(this).val())) ? 0 : parseInt(jQuery(this).val()));
		
		var thumbTitle = parseInt(jQuery("#ExtrasImageModify_dim_x").val()) + "x" + parseInt(jQuery("#ExtrasImageModify_dim_y").val());
		if(thumbTitle == "0x0")
			thumbTitle = "";

		if(thumbTitle == jQuery("#ExtrasImageModify_display_name").val()) {
			titleFollowDim = true;
		}

		if(titleFollowDim) {
			jQuery("#ExtrasImageModify_display_name").val(thumbTitle).keyup();
		}			
	}).focus(function() {
        jQuery(this).select();
    });
	jQuery("#ExtrasImageModify_display_name").keyup(function() {
		var thumbTitle = jQuery("#ExtrasImageModify_dim_x").val() + "x" + jQuery("#ExtrasImageModify_dim_y").val();
		if(thumbTitle == "0x0")
			thumbTitle = "";
		if(thumbTitle == jQuery("#ExtrasImageModify_display_name").val()) {
			titleFollowDim = true;
		} else {
			titleFollowDim = false;
		}
		
	
	});
	
	jQuery("#ExtrasImageModify_mode").change(function() {
		switch(jQuery(this).val()) {
			case "crop":
			case "stretch":
				jQuery(".mode-dep").slideUp();
				break;
			default:	
				jQuery("#ExtrasImageModify_resize").change();
				jQuery(".mode-dep:not(.resize-dep)").slideDown();
		}
	});
	jQuery("#ExtrasImageModify_resize").change(function() {
		if(jQuery(this).is(":checked"))
			jQuery(".resize-dep").slideUp();
		else
			jQuery(".resize-dep").slideDown();
	});
	
	jQuery("#ExtrasImageModify_mode").change();
	jQuery("#ExtrasImageModify_resize").change();
});