jQuery(function(){
	jQuery(".fieldsetwrapper textarea").each(function() {
		jQuery(this).after('<div class="character-left">' + parseInt(255 - jQuery(this).val().length) + '</div>'); 
	});
	jQuery(".fieldsetwrapper textarea").keyup(function(e) {
		if(jQuery(this).val().length > 255)
			jQuery(this).val(jQuery(this).val().substring(0, 255));
		$(this).next().text(255 - jQuery(this).val().length); 
	}); 
});