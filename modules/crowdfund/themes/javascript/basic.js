jQuery(function(){
	jQuery(".teaser textarea").each(function() {
		jQuery(this).after('<div class="character-left">' + "character left : " + parseInt(200 - jQuery(this).val().length) + '</div>'); 
	});
	jQuery(".teaser textarea").keyup(function(e) {
		if(jQuery(this).val().length > 200)
			jQuery(this).val(jQuery(this).val().substring(0, 200));
		$(this).next().text(200 - jQuery(this).val().length);  
	}); 
});