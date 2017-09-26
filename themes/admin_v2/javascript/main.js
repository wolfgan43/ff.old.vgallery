jQuery(function() {

	/* sposto i due menu dentro l'head bar */

	

	
/*	jQuery(".headbar").after('<span class="toggler"></span>');
	jQuery(".toggler").click(function() {
		if(jQuery(".headbar").is(":visible")) {
			jQuery(".notify-handler").hide();
			jQuery(".headbar, .helperlink").slideUp();
			jQuery(this).css({
				"background-position": "-34px -100px",
				"margin-bottom": "50px"
			});
		} else {
			jQuery(".headbar, .helperlink").slideDown();
			jQuery(".notify-handler").show();
			jQuery(this).css({
				"background-position": "8px -90px"
			});
		}
	});
	jQuery(".headbar").after('<span class="viewsubmenu"></span>');
	jQuery(".viewsubmenu").hide();
	jQuery(".viewsubmenu").click(function(){
		jQuery("#menu-list").slideDown();
		jQuery(".viewsubmenu").hide();
	});
	*/
	/* gestione submenu */
	/*
	if(jQuery("#menu-list").is(":visible")) {
		jQuery("#menu-list").append('<span class="resizer"></span>');
		jQuery(".resizer").click(function(){
			if(jQuery(".headbar").is(":visible")) {
				jQuery("#menu-list").slideUp(1000, function() {
					
					jQuery(".viewsubmenu").fadeIn();
					
				});
			} else {
				var i = 0;
				while(i < 3) {
						jQuery("#menu-list").animate({
						left:'-=10',
						duration: 1
					});
						jQuery("#menu-list").animate({
						left:'+=10',
						duration: 1
					});
						i++;
						if(i == 3) {
							jQuery("#menu-list").animate({
							marginLeft:0,
							duration: 1
						});
						}
					}
			}
		});
	}
	*/
	
	
	/* helper */
	var helptext = jQuery("#helper-content").text();
	var helphtml = jQuery("#helper-content").html();
	var graphcount = helptext.split("{");
	
	if( graphcount.length == 1 || graphcount.length > 2 ) {
		jQuery("#helper-content").hide();
		jQuery(".headbar").after('<span class="helperlink"></span>');
		jQuery(".helperlink").click(function() {
			if(jQuery(".helpbox").is(":visible"))
      				 return false;

			jQuery("body").prepend('<div class="eclipse"></div><div class="helpbox"></div>');
			jQuery(".helpbox").html('<div class="helptext" >' + helphtml + '</div>');
			jQuery(".helpbox").prepend('<span class="closebox"></span>');
			jQuery(".closebox").click(function(){
				jQuery(".helpbox").fadeOut();
				jQuery(".eclipse").remove();
			});
		});
	} else {
		jQuery("#helper-content").remove();
	}
	
	
	/* cambio lingua */
	jQuery(".headbar").after('<span class="langchooser"></span>');
	jQuery(".langchooser").hover(function(){
		jQuery("#language-chooser").stop(true,true).slideDown();
	},function(){
		jQuery("#language-chooser").delay(1700).slideUp();
	});

	/* gestione margini menu */
	jQuery("#content").css("padding-top", jQuery("#bar").outerHeight() + jQuery("#menu-list").outerHeight() + 10);
	
	/* resize interfaccia */
	if(jQuery(window).innerWidth() > 1024) {
		jQuery("#content").width(jQuery(window).innerWidth() - 40);
	}
	jQuery(window).resize(function() {
		if(jQuery(window).innerWidth() > 1024) {
			jQuery("#content").width(jQuery(window).innerWidth() - 40);
			jQuery("#content").css("padding-top", jQuery("#bar").outerHeight() + jQuery("#menu-list").outerHeight() + 10);
			
		} else {
			jQuery("#content").width(980);
			jQuery("#content").css("padding-top", jQuery("#bar").outerHeight() + jQuery("#menu-list").outerHeight() + 10);
			
		}
	});
	
		

	
	/* ----- filtri forms ----- */
	jQuery("#FormConfigModify_data").before('<a href="javascript:void(0);" class="advanced-link"></a>');
//	jQuery(".advanced").hide();
//	jQuery("#FormConfigModify_limit_by_groups_0").closest("fieldset").hide();
	

});




