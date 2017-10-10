jQuery(function() {
	ff.pluginAddInit("ff.cms.admin", function() {
		var container = jQuery('.dep-container-grid INPUT[type="hidden"][value!="0"]').closest("fieldset");
		jQuery(container).each(function() {
			//jQuery(this).find(".check-container-grid INPUT").click();
		});
		
		jQuery(".fluid-def SELECT").change(); 
	});
});