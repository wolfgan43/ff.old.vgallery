function resizeBar(){
	
	jQuery(".order-payment").width( jQuery(".row").width() - 223 - jQuery(".order-anagraph").width() - jQuery(".order-shipping").width() );
	jQuery(".order-payment TABLE").width(jQuery(".order-payment").innerWidth());
};

resizeBar();
jQuery(window).resize(function(){
	resizeBar();
});

jQuery(".row").hover(function(){
	jQuery(this).stop(true, true).animate({ 'backgroundColor' : '#fff' }, 200);
	jQuery(this).children(".order-bill").stop(true, true).animate({ 'backgroundColor' : '#dedede' }, 200);
}, function(){
	jQuery(this).stop(true, true).animate({ 'backgroundColor' : '#f9f9f9' }, 200);
	jQuery(this).children(".order-bill").stop(true, true).animate({ 'backgroundColor' : '#e9e9e9' }, 200);
});


jQuery(".wrap-more").hover(function(){
	jQuery(this).children(".order-more").stop(true, true).fadeIn(200);
}, function(){
	jQuery(this).children(".order-more").stop(true, true).fadeOut(200);
});



/*jQuery("#Order .row .order-payment").each(function(){
	if (jQuery(this).find(".total-bar").is("div")) {
		var payed = parseFloat(jQuery(this).find(".total-bar").attr("payed"));
		var totBill = parseFloat(jQuery(this).find(".total-bar").attr("total"));
		var totalWidth = jQuery(this).find(".total-bar").find(".disp-tot").outerWidth() -1;
		var chargeWidth = 0;
		if(payed > totBill) {

			chargeWidth = parseInt(totalWidth) + ((parseInt(payed) - parseInt(totBill)) * (parseInt(jQuery(this).find(".total-bar").outerWidth() - 1 ) - parseInt(totalWidth )) / parseInt(totBill) );
			if(chargeWidth > parseInt(jQuery(this).find(".total-bar").outerWidth() - 1)) {
				chargeWidth = parseInt(jQuery(this).find(".total-bar").outerWidth() - 1);
			}
		} else {
			chargeWidth = parseInt(payed) * parseInt(totalWidth ) / parseInt(totBill);
		}

		jQuery(this).find(".total-bar").find(".charge").width("0");
		jQuery(this).find(".total-bar").find(".charge").animate({
				width: chargeWidth,
			}, 2000, function() {
			// Animation complete.
		});
	}
});*/

jQuery("#Order .row .order-payment").each(function(){
	if (jQuery(this).find(".total-bar").is("div")) {
		var payed = parseFloat(jQuery(this).find(".total-bar").attr("payed"));
		var totBill = parseFloat(jQuery(this).find(".total-bar").attr("total"));
		var ratio = totBill/payed;
		var percentage = 100/ratio + "%";

		if (parseInt(percentage) > 105 ) {
			percentage = "105%";
			jQuery(this).find(".charge").css("background-color", "#df522e");
			jQuery(this).find(".disp-tot").css({"border-color" : "#6c2816", "color": "#6c2816"});
		};

		if (parseInt(percentage) > 100 ) {
			jQuery(this).find(".charge").css("background-color", "#df522e");
			jQuery(this).find(".total-bar").css("color", "#6c2816");
		};

		jQuery(this).find(".total-bar").find(".charge").animate({
				width : percentage
		}, 2000);


	};
});

/*
jQuery(".row .order-payment").each(function(){

	if (jQuery(this).find(".total-bar").find(".disp-tot").is("span") ) {
		var Totals = jQuery(this).find(".total-bar").find(".disp-tot").text().split("|");
		var payed = Totals[0].trim().split(" ")[0].replace(".","").replace(",",".");

		var totBill = Totals[1].trim().split(" ")[0].replace(".","").replace(",",".");
		var totalWidth = jQuery(this).find(".total-bar").find(".disp-tot").outerWidth() -1;
		alert(payed + " " + totBill);
		alert(jQuery(this).find(".total-bar").find(".disp-tot").outerWidth());


		//var totBill = parseFloat(jQuery(this).find(".total-bar").find(".disp-tot").text().replace(".","").replace(",","."));
	    //var symbol = jQuery(this).next(".total-bar").find(".disp-tot").text().split(" ")[1];

	    if( payed > 0) {							
			//jQuery(this).closest(".row").find(".payed").each(function() {
	 		//	payed = payed + parseFloat(jQuery(this).text().replace(".","").replace(",","."));
			//});

			
			
			//jQuery(this).next(".total-bar").find(".disp-tot").html(ff.numberToCurrency(payed) + " " + symbol + " | " + jQuery(this).next(".total-bar").find(".disp-tot").text());
			var chargeWidth = 0;
	        if(parseInt(payed) > parseInt(totBill)) {
				chargeWidth = parseInt(totalWidth) + ((parseInt(payed) - parseInt(totBill)) * (parseInt(jQuery(this).find(".total-bar").outerWidth() - 1 ) - parseInt()) / parseInt(totBill) );
				if(chargeWidth > parseInt(jQuery(this).find(".total-bar").outerWidth() - 1)) {
					chargeWidth = parseInt(jQuery(this).find(".total-bar").outerWidth() - 1);
				}
			} else {
				chargeWidth = parseInt(payed) * parseInt(jQuery(this).next(".total-bar").find(".disp-tot").outerWidth() - 1 ) / parseInt(totBill);
			}

	        jQuery(this).find(".total-bar").find(".charge").width("0");
	        jQuery(this).find(".total-bar").find(".charge").animate({
					width: chargeWidth,
				}, 2000, function() {
				// Animation complete.
			});
			//jQuery(this).next(".disp-tot").children(".charge").width(chargeWidth);
	                        			
		} else {
			jQuery(this).find(".total-bar").find(".charge").width("0");
			jQuery(this).find(".total-bar").find(".disp-tot").text(" n/a | " + jQuery(this).next(".total-bar").find(".disp-tot").text());
		}
	}
});*/
/*jQuery("#Order .row .order-header").click(function() {
	if(jQuery(this).parent().hasClass("opselected")) {
		jQuery(this).parent().removeClass("opselected");
	} else {
		jQuery(this).parent().addClass("opselected");
	}
});*/

/* dimensione dinamica dei pagamenti */
/*var paymentWidth = jQuery(".row:first").innerWidth() - jQuery(".row:first .order-price").outerWidth() - jQuery(".row:first .shipping").outerWidth() - 60;

jQuery(".row .order-payment").css("width", paymentWidth + "px");
jQuery(window).resize(function() {
	var paymentWidth = jQuery(".row:first").innerWidth() - jQuery(".row:first .order-price").outerWidth() - jQuery(".row:first .shipping").outerWidth() - 60; 
	jQuery(".row .order-payment").css("width", paymentWidth + "px");
});
/*

/*
	jQuery("#Order .row").click(function() {
		if(jQuery(this).hasClass("opselected")) {
			jQuery(this).removeClass("opselected");
		} else {
			jQuery(this).addClass("opselected");
		}
	});
	*/
	


/*
	jQuery("#Order .row").hover(
			function()	{
				jQuery("#Order .row").removeClass("opselected");
  				jQuery(this).addClass("opselected");
        },
            function()
        {
                //jQuery(this).children(".order-price, .order-payment, .shipping").stop(true,true).delay("300").slideUp("fast");
        }
	);
*/