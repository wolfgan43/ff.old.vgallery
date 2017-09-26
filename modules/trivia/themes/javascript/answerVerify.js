ff.fn.trivia  = (function () { 
			
	var that = 
	{
		"init" : function()
		{  
			if(jQuery(".control-answer").length) {
				jQuery(".google-search").hide();
				jQuery(".trivia-question-answer").hide();
				jQuery(".achievement").hide();
				jQuery(".step-question").hide();

				jQuery(".question").click(function() 
				{
					window.location.href = window.location.pathname
					//ff.fn.trivia.page();
				});
				setTimeout(function() {
					window.location.href = window.location.pathname;
					//ff.fn.trivia.page();
				}, 4000);
			} else if(jQuery(".step-question").length) {
				jQuery(".google-search").hide();
				jQuery(".trivia-question-answer").hide();
				jQuery(".achievement").hide();
				jQuery(".continue-gioca").click(function() 
				{
					jQuery(".step-question").remove();
					ff.fn.trivia.pageElement();
				});
			} else {
				jQuery(".google-search").hide();
				setTimeout(function() {
			   		jQuery(".google-search").fadeIn();
				}, 30000);				
				jQuery(".countdown").attr("counter", "1");
				ff.fn.trivia.countdown();
			}
		},
		"page" : function()
		{
			if(jQuery(".control-answer").is(':visible'))
			{
				
				if(jQuery(".step-question").length)   
				{

					jQuery(".step-question").show();
					jQuery(".control-answer").hide(); 
					if(jQuery(".achievement").length)   
					{
						var i = 1;
						jQuery(".achievement").show();
						jQuery(".new-step-achievement").hide(); 
						jQuery("." + i).show();
						jQuery(".continue").click(function() 
						{
							jQuery("." + i).remove();
							i++;
							if(jQuery("." + i).length)   
							{
								jQuery("." + i).show();
							} else
							{
								jQuery(".achievement").remove();
							}
						});
					}
				} else
				{
					ff.fn.trivia.pageElement();
				}
			}
		},
		"pageElement": function()
		{
			jQuery.ajax({
				url: ff.site_path + "/services/question" + window.location.pathname,   
				success: function(data) 
				{
					jQuery(".control-answer").hide();
					jQuery(".trivia-question-answer").show();
					if(jQuery(".trivia-question-answer").is(':visible'))
					{
						jQuery(".countdown").attr("counter", "1");
						ff.fn.trivia.countdown();
						setTimeout(function() {
					   		jQuery(".google-search").fadeIn();
						}, 30000);
					}
				}
			});
		},
		"countdown": function()
		{
			setTimeout(function() {
				if(jQuery(".trivia-question-answer").is(':visible')) {
					var counterSec = parseInt(jQuery(".countdown").attr("counter")) + 1;
					
					var widthProgress = jQuery(".countdown").width() * counterSec / 30;
					var bgColor = ""; 
					if(counterSec <= 30) {
						jQuery(".countdown").attr("counter", counterSec);
						
						switch(counterSec) {
							case 10:
								bgColor = "FBB900";
							case 20: 
								if(!bgColor)
									bgColor = "EE5A00";

								jQuery(".countdown .progress").fadeOut("fast", function() {
									jQuery(this).css({"background-color" : "#" + bgColor
														, "width" : widthProgress
													});
									jQuery(this).fadeIn("fast");
								});
								break;
							default:
								jQuery(".countdown .progress").width(widthProgress);
						}
						
					} 
					
					ff.fn.trivia.countdown();
				} else {
					jQuery(".countdown").attr("counter", "1");
				}
			}, 1000);
		}
	};
	return that; 
	
})();
jQuery(function(){
	ff.fn.trivia.init();
});