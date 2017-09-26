/*
if(!jQuery(".filterbuttons .filter").is("a")) {
	
	jQuery("DIV.filter").hide();

	jQuery(".filter").click(function(){
		if(jQuery(".filter:not(a)").is(":visible")) {
			return false;
		} else {
			jQuery("body").prepend('<div class="eclipse"></div>');
			jQuery(".filter:not(a)").addClass("helpbox").fadeIn();
			jQuery(".helpbox").prepend('<span class="closebox"></span>');
			jQuery(".closebox").click(function(){
				jQuery(".filter:not(a)").hide();
				jQuery(".eclipse").remove();
				jQuery(".filter:not(a)").removeClass("helpbox");
				jQuery(this).remove();
			});
		}
		return false;
	});	
}*/



/* remove column function */
jQuery.fn.removeCol = function(col){
	// Make sure col has value
	if(!col){ col = 1; }
	jQuery('tr td:nth-child('+col+'), tr th:nth-child('+col+')', this).hide();
	return this;
};



/*	jQuery(".total").find("table").children("tbody").children("tr:not(:last)").each(function() {
	jQuery(this).hide();
});
*/
jQuery(function() {
	jQuery(".total").find("table").addClass("compactable");
	jQuery(".total").find("table").removeCol();
	
	jQuery(".total").find("table").children("tbody").children("tr:not(:last)").hide();

	jQuery(".toggle").click(function(){
		if(jQuery(".total").find("table").hasClass("compactable")) {
			jQuery(".total").find("table").removeClass("compactable");
			jQuery('.total tr td:nth-child(1), .total tr th:nth-child(1)').show();
			
			jQuery(this).attr("class", jQuery(this).attr("class").replace("expand", "compress"));
			jQuery(".total").find("table").children("tbody").children("tr:not(:last)").fadeIn();
		} else {
			jQuery(".total").find("table").addClass("compactable");		
			jQuery(".total").find("table").removeCol();
			
			jQuery(this).attr("class", jQuery(this).attr("class").replace("compress", "expand"));
			jQuery(".total").find("table").children("tbody").children("tr:not(:last)").fadeOut();
		}
		return false;
	});

});