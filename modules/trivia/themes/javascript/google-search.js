jQuery(function(){
	jQuery(".google-search").hide();
	if(jQuery(".triva-question").is(':visible'))
	{
		setTimeout(function() {
	   		jQuery(".google-search").show();
		}, 30000);
	}
});  