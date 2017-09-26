jQuery(function(){
	showAchievement();
});

function showAchievement(){
	if(jQuery(".achievement").length > 0)   
	{
		jQuery(".achievement").show();
		
		var i = 1;
		jQuery(".new-achievement").hide();
		jQuery("." + i).fadeIn();
		jQuery(".continue").click(function() 
		{
			jQuery("." + i).remove();
			i++;
			if(jQuery("." + i).length > 0)   
			{
				jQuery("." + i).fadeIn();
			} else
			{
				jQuery(".achievement").hide();
			}
		});
	}
}