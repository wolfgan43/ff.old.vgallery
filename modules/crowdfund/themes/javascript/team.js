jQuery(function(){
	if($("#IdeaModify_team").val() === "1") 
	{
		jQuery(".row.number_member").fadeIn();
		jQuery("#IdeaModify_team").live("change", function() {
			if($("#IdeaModify_team").val() === "1") 
			{
				jQuery(".row.number_member").fadeIn();   
				jQuery("#IdeaModify_number_member").live("change", function() {
					jQuery(".row.member").fadeIn();
				});
			} else  
			{	
				jQuery(".row.number_member").hide();
			}
		});
	} else
	{
		jQuery(".row.number_member").hide();
		jQuery(".row.member").hide();  
		jQuery("#IdeaModify_team").live("change", function() {
			if($("#IdeaModify_team").val() === "1") 
			{
				jQuery(".row.number_member").fadeIn();   
				jQuery("#IdeaModify_number_member").live("change", function() {
					jQuery(".row.member").fadeIn();
				});
			} else  
			{	
				jQuery(".row.number_member").hide();
			}
		});
	}
});