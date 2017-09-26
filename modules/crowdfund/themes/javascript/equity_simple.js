var startEquity = 0;
var startGoal = 0;
var startGoalStep = 0;

function calcEquityTotal() {
	var equity = 0;
	var goal = 0;
	var goalStep = 0;
	
	if($( "#IdeaModify_equity").val().split(".").length - 1 > 1 || $( "#IdeaModify_equity").val().indexOf(",") > 0) {
		equity = parseFloat($( "#IdeaModify_equity").val().replace(/\./g, "").replace(",", ".")); 
	} else {
		equity = parseFloat($( "#IdeaModify_equity").val());
	}
	if(isNaN(equity))
		equity = 0; 

	
	if($( "#IdeaModify_goal").val().split(".").length - 1 > 1 || $( "#IdeaModify_goal").val().indexOf(",") > 0) {
		goal = parseFloat($( "#IdeaModify_goal").val().replace(/\./g, "").replace(",", "."));
	} else {
		goal = parseFloat($( "#IdeaModify_goal").val());
	}
	if(isNaN(goal))
		goal = 0; 
	
	if($( "#IdeaModify_goal_step").val().split(".").length - 1 > 1 || $( "#IdeaModify_goal_step").val().indexOf(",") > 0) {
		goalStep = parseFloat($( "#IdeaModify_goal_step").val().replace(/\./g, "").replace(",", "."));
	} else {
		goalStep = parseFloat($( "#IdeaModify_goal_step").val());
	}
	if(isNaN(goalStep))
		goalStep = 0; 



	minEquity = (goalStep * 100 / goal);
	if(isNaN(minEquity))
		minEquity = 0;

	goalStepEquity = minEquity * (equity / 100);
	
	if(minEquity > 100) {
		goalStep = $("#IdeaModify_goal_step_slider").slider( "option", "min");
		jQuery(".min-equity span").text("100" + "%");
		jQuery("#IdeaModify_goal_step_amount").val(ff.numberToCurrency(goalStep, ",", "").replace(",00", ""));
		jQuery("#IdeaModify_goal_step").val(ff.numberToCurrency(goalStep, ",", "").replace(",00", "")); 
		
		$("#IdeaModify_goal_step_slider").slider( "option", "max", goal);
		$("#IdeaModify_goal_step_slider").slider( "value", goalStep);
	} else {
		jQuery(".min-equity span").text(ff.numberToCurrency(minEquity) + "%");
		$("#IdeaModify_goal_step_slider").slider( "option", "max", goal);
		jQuery("#IdeaModify_goal_step").val(ff.numberToCurrency(goalStep, ",", "").replace(",00", "")); 
	}
	maxShare = ff.numberToCurrency(goal / goalStep, ",", "");
	jQuery(".max-share span").text(maxShare.replace(",00", ""));
}

jQuery(function(){
	if(window.location.pathname.indexOf("/reward") >= 0) {
		if($("#IdeaModify_equity").length > 0) {
			if($("#IdeaModify_equity").val().split(".").length - 1 > 1 || $( "#IdeaModify_equity").val().indexOf(",") > 0) {
				startEquity = parseFloat($( "#IdeaModify_equity").val().replace(/\./g, "").replace(",", "."));  
			} else {
				startEquity = parseFloat($( "#IdeaModify_equity").val());  
			}
			if(isNaN(startEquity))
				startEquity = 0; 
		}

		if($("#IdeaModify_goal").length > 0) {
			if($( "#IdeaModify_goal").val().split(".").length - 1 > 1 || $( "#IdeaModify_goal").val().indexOf(",") > 0) {
				startGoal = parseFloat($( "#IdeaModify_goal").val().replace(/\./g, "").replace(",", "."));
			} else {
				startGoal = parseFloat($( "#IdeaModify_goal").val());
			}
			if(isNaN(startGoal))
				startGoal = 0; 
		}
		
		if($("#IdeaModify_goal_step").length > 0) {
			if($( "#IdeaModify_goal_step").val().split(".").length - 1 > 1 || $( "#IdeaModify_goal_step").val().indexOf(",") > 0) {
				startGoalStep = parseFloat($( "#IdeaModify_goal_step").val().replace(/\./g, "").replace(",", "."));
			} else {
				startGoalStep = parseFloat($( "#IdeaModify_goal_step").val());
			}
			if(isNaN(startGoalStep))
				startGoalStep = 0; 
		}

		// jQuery(".symbol").hide();
		 jQuery("#IdeaModify_enable_equity").click(function() {
			if(jQuery(this).is(":checked")) {
				 jQuery(".row.goal").fadeIn(); 
				 jQuery(".row.equity").fadeIn();
				 jQuery(".row.goal_step").fadeIn();
			 } else {
				 jQuery(".row.goal").hide(); 
				 jQuery(".row.equity").hide();
				 jQuery(".row.goal_step").hide();
			 }
		 }); 
		 jQuery("#IdeaModify_enable_pledge").click(function() {
			if(jQuery(this).is(":checked")) {
				 jQuery(".row #Pledge").fadeIn();
			 } else {
				 jQuery(".row #Pledge").hide();
			 }
		 }); 	 

		$( "#IdeaModify_equity_slider").on( "slidechange", function( event, ui ) {
			calcEquityTotal();
		});
		
		$( "#IdeaModify_goal_step_slider").on( "slidechange", function( event, ui ) {
			calcEquityTotal();
		});	
		
		jQuery("#IdeaModify_equity_amount").keyup(function() { 
			setTimeout("calcEquityTotal();", 100);
		});

		jQuery(".increase-capital").click(function() {
			$( "#IdeaModify_equity_slider").slider( "value", startEquity );
			jQuery("#IdeaModify_equity_amount").val(startEquity);
			jQuery("#IdeaModify_equity").val(startEquity);
			
			
			jQuery("#IdeaModify_goal").val(ff.numberToCurrency(startGoal));
			jQuery("#IdeaModify_goal_label").text(ff.numberToCurrency(startGoal));
			
			$( "#IdeaModify_goal_step_slider").slider( "value", startGoalStep );
			jQuery("#IdeaModify_goal_step_amount").val(startGoalStep);
			jQuery("#IdeaModify_goal_step").val(startGoalStep);
			
			calcEquityTotal();
		});

		 if(jQuery("#IdeaModify_enable_equity").is(":checked")) {
			 jQuery(".row.goal").fadeIn(); 
			 jQuery(".row.equity").fadeIn(); 
			 jQuery(".row.goal_step").fadeIn();
		 } else {
			 jQuery(".row.goal").hide(); 
			 jQuery(".row.equity").hide();
			 jQuery(".row.goal_step").hide();
		 }	

		 if(jQuery("#IdeaModify_enable_pledge").is(":checked")) { 
			 jQuery(".row #Pledge").fadeIn(); 
		 } else {
			 jQuery(".row #Pledge").hide();
		 }	
	}
});