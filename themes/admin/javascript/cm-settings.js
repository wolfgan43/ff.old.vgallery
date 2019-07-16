jQuery(function() {
	jQuery("#settings").before('<div class="menu-settings"><ul></ul></div>');
	jQuery("#settings FIELDSET:first").fadeIn();
	
	var menuItems = "";
	
	jQuery("LEGEND").each(function() {
		menuItems = menuItems + '<li><a href="javascript:void(0);" rel="' + jQuery(this).text() + '">' + jQuery(this).text() + '</a></li>';
	});
	
	jQuery(".menu-settings ul").append(menuItems);
	
	jQuery(".menu-settings A").click(function() {
		jQuery(".menu-settings A").removeClass("current");
		jQuery(this).addClass("current");
		
		jQuery("#settings FIELDSET").hide();
		
		var ref = jQuery(this).attr("rel");
		
		jQuery("LEGEND").each(function() {
			if(jQuery(this).text() == ref ) {
				jQuery(this).parent().fadeIn();
			} else {
			
			}
		});
	});
});