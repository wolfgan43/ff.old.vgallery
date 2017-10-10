jQuery(function() {
	var gUrl = '';
	
	jQuery(".gdocsReload").each(function() {
		if(jQuery(this).prev().val().length) {
			gUrl = 'https://{service}{mode}?key=' + jQuery(this).prev().val() + '&h1={lang}&token={token}';
			alert(gUrl);
			jQuery(this).next().attr('src', gUrl);
			jQuery(this).next().reload;
		}
	
	});
	
	
	jQuery(".gdocsReload").click(function() {
		if(jQuery(this).prev().val().length) {
			gUrl = 'https://{service}{mode}?key=' + jQuery(this).prev().val() + '&h1={lang}&token={token}';
			alert(gUrl);
			jQuery(this).next().attr('src', gUrl);
			jQuery(this).next().reload;
		}
	
	});


});