function characterLeft (long, field){
	jQuery(field).each(function() 
	{
		if(jQuery(this).next().hasClass("character-left") === true)
		{
			$(this).next().text(long - jQuery(field).val().length); 
		}
		else
		{
			jQuery(this).after('<div class="character-left">' + parseInt(long - jQuery(this).val().length) + '</div>');
		}
	});
};