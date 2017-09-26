jQuery(function(){
	jQuery(".trivia-question-answer").hide();
	jQuery(".continue-gioca").click(function() {
		jQuery(".step-question").remove();
		jQuery(".trivia-question-answer").show();
		setTimeout(function() {
	   		jQuery(".google-search").show();
		}, 30000);
	}); 
});