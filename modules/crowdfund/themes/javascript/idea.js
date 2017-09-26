function CFideaDialog(id, smartUrl, type, value, price) {
    valuta = value;
    control = 1;
    //jQuery(window).scrollTop(0);
	
	jQuery("#L"+id).wrap('<div class="cf-overlay"></div>');
	jQuery("body").css("overflow", "hidden");
	
    if(jQuery("." + smartUrl).find("h1").text()) {
        jQuery("#L" + id + " .form_Progetto span").text(jQuery("." + smartUrl + ":eq(0)").find("h1").text());
        jQuery("#L" + id + " .form_Progetto input").val(jQuery("." + smartUrl + ":eq(0)").find("h1").text());
    } else {
        jQuery("#L" + id + " .form_Progetto span").text(jQuery("." + smartUrl + ":eq(0)").find("h3").text());
        jQuery("#L" + id + " .form_Progetto input").val(jQuery("." + smartUrl + ":eq(0)").find("h3").text());
    }

    jQuery("#L" + id + " .form_Messaggio.row").hide();

    jQuery('body').bind('click.L' + id, function(e) {
        if(jQuery(e.target).closest('#L' + id).length  == 0) {
            jQuery("#L" + id).fadeOut(function() {
                jQuery('body').unbind('click.L' + id);
		jQuery("#L"+ id ).unwrap();	
		jQuery("body").css("overflow", "visible");
		
		
            });
        }
    });
    
	

    switch(type) {
        case "equity":
            ff.pluginLoad("help_tip","/modules/crowdfund/themes/javascript/help_tip.js", function() {

            });
            jQuery("#L" + id + " .form_Prezzo.row INPUT").attr("disabled", "disabled").parent().attr("smartUrl", smartUrl).hide();
            jQuery("#L" + id + " .form_prezzovaluta.row INPUT").hide().parent().hide();
            jQuery("#L" + id + " .form_Equityglobale.row INPUT").hide().parent().hide();
            jQuery(".form_Prezzo.row INPUT").after( '<span class="prezzo-valuta">' + valuta + '</span>' );


            //jQuery(".form_Prezzo.row INPUT, .form_Equityglobale.row INPUT").attr("disabled", "disabled").parent().hide();
            jQuery("#L" + id + " INPUT.checkgroup").attr('checked', false); 
            break;
        case "pledge":
            if(value) {
                jQuery("#L" + id + " .form_Prezzo.row SPAN").text(price);
                jQuery("#L" + id + " .form_Prezzo.row INPUT").val(value);
            }
            break;
        case "donation": 
            jQuery("INPUT#MD-Content-form-dona_14").on("change", function(){
                    jQuery("#L" + id + " .form_Valuta.row INPUT").val(jQuery("#L" + id + " .form_Prezzo.row INPUT").val() + value);
            });
            break; 
        default:
    }
    jQuery("#L" + id ).fadeIn("fast");
}



jQuery(document).on("change", "INPUT#MD-Content-form-investi_9_0", function(){
    jQuery(".form_Prezzo.row").stop(true, true).fadeToggle();
	jQuery(".form_Equityglobale.row").stop(true, true).fadeToggle();
	
	if ((jQuery(".form_Prezzo.row INPUT").attr("disabled") === undefined || jQuery(".form_Prezzo.row INPUT").attr("disabled")) && control === 1) {
            jQuery(".form_Prezzo.row INPUT").removeAttr("disabled");
            jQuery(".form_Prezzo.row INPUT SPAN.prezzo-valuta").show();

            jQuery("INPUT#MD-Content-form-investi_7").on("change", function(){
                jQuery(".form_Valuta.row INPUT").val(jQuery("INPUT#MD-Content-form-investi_7").val() + valuta);
            });
            control = 0;
	} else {
            jQuery(".form_Prezzo.row INPUT").attr("disabled", "disabled");
            jQuery(".form_Prezzo.row INPUT SPAN.prezzo-valuta").hide();
            control = 1;
	};

});

jQuery(document).on("change", "INPUT#MD-Content-form-investi_9_1", function(){
    jQuery(".form_Messaggio.row").stop(true, true).fadeToggle();
});

jQuery(document).on("change", "INPUT#MD-Content-form-investi_9_2", function(){
    jQuery(".form_Messaggio.row").stop(true, true).fadeToggle();
});

jQuery(document).on("keyup", "INPUT#MD-Content-form-investi_7", function() {
    var elem = jQuery(this);
    if(parseInt(jQuery(this).val()) == jQuery(this).val()) { 
        jQuery.getJSON("/services/get-equity/" + jQuery(this).parent().attr("smartUrl") + "?price=" + jQuery(this).val(), function(data){
                if(parseInt(data.maxequity) >= parseInt(data.equity)) {
                        jQuery(".form_Equityglobale.row LABEL").text("Equity: " + data.equity + "%") ;
                        jQuery(".form_Equityglobale.row INPUT").val(data.equity + "%");
                } else {
                        jQuery(elem).val(jQuery(elem).val().substring(0, jQuery(elem).val().length - 1 ));
                }
        });
    } else {
        jQuery(elem).val(jQuery(elem).val().substring(0, jQuery(elem).val().length - 1 ));
    }
});

/*
function L32(data) {
	if (jQuery("#L32 .error").length == 0 ) {
			jQuery("#L32").hide();
	}
};

function L38(data) {
	if (jQuery("#L38 .error").length == 0 ) {
			jQuery("#L38").hide();
	}
};
function L48(data) {
	if (jQuery("#L48 .error").length == 0 ) {
			jQuery("#L48").hide();
	}
};
ff.cms.e.L49 = function(data) {
	if (jQuery("#L49 .error").length == 0 ) {
			jQuery("#L49").hide();
	}
};
*/

function CFollow(elem, smartUrl) {
    var el = elem;

    jQuery.getJSON("/services/follow/" + encodeURIComponent(smartUrl), function(data) {
        jQuery(el).attr("class", "ffButton follow");
        jQuery(el).text(data["label"]);

        if(data["class"].length > 0) {
            jQuery(el).addClass(data["class"]);
        }
    });
       
}
