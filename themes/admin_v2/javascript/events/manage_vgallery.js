/* tabella magazzino */
if(!jQuery(".legend").is("div")) {
	if(jQuery(".location_stock").length > 0 || jQuery(".actual_stock").length > 0) {
		if(jQuery(".location_stock").length > 0) {
			var legend = '<div class="legend"><span class="warehouse"></span> magazzino - <span class="stockincome"></span> stock in arrivo - <span class="stockavailable"></span> stock disponibili - <span class="stockrequested"></span> stock richiesti</div>';
		} else if(jQuery(".location_stock").length <= 0) {
			var legend = '<div class="legend"><span class="stockincome"></span> stock in arrivo - <span class="stockavailable"></span> stock disponibili - <span class="stockrequested"></span> stock richiesti</div>';
		}
		jQuery(".listview").children(".heading").prepend(legend);
		jQuery(".legend span").css("display","inline-block");
	} 
}