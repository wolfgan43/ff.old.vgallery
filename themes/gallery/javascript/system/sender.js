jQuery(function() {
	jQuery(".sender .hide").hide();
	jQuery(".sender a.send, .sender a.re-send").click(function() {
		var target_container = jQuery(this).closest(".sender");
		var elem = jQuery(this);
		
		jQuery(elem).addClass("wait");
		jQuery.post(ff.site_path + "/srv/sender" + jQuery(target_container).find(".reference").val()
			, { 
				name : jQuery(target_container).find(".name input").val()
				, email : jQuery(target_container).find(".mail input").val()
			}
			, function(data) {
				jQuery(elem).removeClass("wait");
				if(jQuery(elem).hasClass("send")) {
					jQuery(target_container).find(".re-send").fadeIn();
					jQuery(elem).hide();
				}
				if(data.status == "ok") {
					jQuery(target_container).html(data.error);
				} else {
					jQuery(target_container).find(".error").html(data.error);
					jQuery(target_container).find(".to").slideDown();
				}
			}
		);
		
	});
});