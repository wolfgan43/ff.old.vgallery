jQuery(function(){
	var i = 1;
	if(jQuery(".achievement").length == true)   
	{
		jQuery(".new-achievement").hide(); 
		jQuery("." + i).show();
		jQuery(".continue").click(function() 
		{
			jQuery("." + i).remove();
			i++;
			if(jQuery("." + i).length == true)   
			{
				jQuery("." + i).show();
			} else
			{
				jQuery(".achievement").remove();
			}
		});
	}
});