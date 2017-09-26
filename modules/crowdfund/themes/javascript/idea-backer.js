	function ideaBackerInit() {
		jQuery("input[name=reward]").closest("TR").click(function() {
			jQuery(this).find("input[name=reward]").attr("checked", true);
			jQuery("#BackersModify_price_amount").val(jQuery(this).find("input[name=reward]").attr("price"));
			jQuery("#BackersModify_price_slider").slider( "value", jQuery(this).find("input[name=reward]").attr("price"));
			jQuery("#BackersModify_price").val(jQuery(this).find("input[name=reward]").attr("price")); 
			jQuery("#BackersModify_ID_reward").val(jQuery(this).find("input[name=reward]").val());
		});

		jQuery("#BackersModify_price_slider").on( "slidechange", function( event, ui ) {
			var slider = this;
			if(jQuery("input[name=reward][price=0]").is(":checked")) {
			} else {
				jQuery("input[name=reward]").removeAttr("checked"); 

				for(var i=jQuery("input[name=reward]").length - 1; i > 0; i--) {    
					if(jQuery(slider).slider("option", "value") >= jQuery("input[name=reward]").eq(i).attr("price")) {  
						jQuery("input[name=reward]").eq(i).attr("checked", "checked"); 
						break;
					}
				};
				
			}
			
		});
	}

jQuery(function() {
	ideaBackerInit();
});
	