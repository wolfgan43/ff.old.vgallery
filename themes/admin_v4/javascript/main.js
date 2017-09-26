//jQuery(function() {
//	
//	jQuery("#header").before('<div id="bar" class="headbar"></div>');
//	
//	/* sposto il nome cliente in basso */
//	var sitetitle = jQuery("#header").children("h1").text();
//	jQuery("#header").children("h1").remove();
//
//	jQuery("#container").after('<div class="footersite">' + sitetitle + '</div>');
//
//	/* sposto i due menu dentro l'head bar */
//	var quicklinks = jQuery("#quick-panel").html();
//	var mainmenu = jQuery("#menu").html();
//
//	jQuery(".headbar").prepend("" + quicklinks + "");
//	jQuery(".headbar ul:first-child").addClass("quicklinks");
//	jQuery(".quicklinks").after("" + mainmenu + "");
//	jQuery(".quicklinks").next("UL:first").addClass("mainmenu");
//	var maxLI = 0;
//	var currentLI = 0;
//	
//	jQuery(".mainmenu li").each(function(cont) {
//		if(jQuery(this).width() > parseInt(maxLI)) {
//			maxLI = jQuery(this).width();
//		}
//		if(jQuery(this).children("a").hasClass("current")) {
//			currentLI = cont;
//		}
//	});
//	var totLI = jQuery(".mainmenu").children("li").length;
//	var widthLI = Math.ceil(700 / parseInt(totLI));
//	
//	var widthUL = widthLI * parseInt(totLI);
//	if(maxLI > widthLI) {
//		jQuery(".mainmenu").width(widthUL);
//		jQuery(".mainmenu li").width(widthLI);
//		jQuery(".mainmenu li a").width(maxLI);
//	
//		ff.pluginLoad("jquery.fn.kwicks", ff.site_path + "/themes/library/plugins/jquery.kwicks/jquery.kwicks.js",function(){
//			jQuery(".mainmenu").addClass("kwicks");
//			jQuery(".mainmenu").kwicks({  
//			        max: maxLI,
//					duration: 400,
//					sticky: true,
//					defaultKwick: currentLI
//			});
//		}, true);
//	} else {
//		jQuery(".mainmenu").width(widthUL);
//		jQuery(".mainmenu li").width(widthLI);
//	}
//
//	/* rimuovo i blocchi originali */
//	jQuery("#quick-panel, #menu").remove();
//
//	
///*	jQuery(".headbar").after('<span class="toggler"></span>');
//	jQuery(".toggler").click(function() {
//		if(jQuery(".headbar").is(":visible")) {
//			jQuery(".notify-handler").hide();
//			jQuery(".headbar, .helperlink").slideUp();
//			jQuery(this).css({
//				"background-position": "-34px -100px",
//				"margin-bottom": "50px"
//			});
//		} else {
//			jQuery(".headbar, .helperlink").slideDown();
//			jQuery(".notify-handler").show();
//			jQuery(this).css({
//				"background-position": "8px -90px"
//			});
//		}
//	});
//*/
//	/* gestione submenu */
//	jQuery(".headbar").after('<span class="viewsubmenu"></span>');
//	jQuery(".viewsubmenu").hide();
//	jQuery(".viewsubmenu").click(function(){
//		jQuery("#menu-list").slideDown();
//		jQuery(".viewsubmenu").hide();
//	});
//	
//	if(jQuery("#menu-list").is(":visible")) {
//		jQuery("#menu-list").append('<span class="resizer"></span>');
//		jQuery(".resizer").click(function(){
//			if(jQuery(".headbar").is(":visible")) {
//				jQuery("#menu-list").slideUp(1000, function() {
//					
//					jQuery(".viewsubmenu").fadeIn();
//					
//				});
//			} else {
//				var i = 0;
//				while(i < 3) {
//						jQuery("#menu-list").animate({
//						left:'-=10',
//						duration: 1
//					});
//						jQuery("#menu-list").animate({
//						left:'+=10',
//						duration: 1
//					});
//						i++;
//						if(i == 3) {
//							jQuery("#menu-list").animate({
//							marginLeft:0,
//							duration: 1
//						});
//						}
//					}
//			}
//		});
//	}
//	/* helper */
//	var helptext = jQuery("#helper-content").text();
//	var helphtml = jQuery("#helper-content").html();
//	var graphcount = helptext.split("{");
//	
//	if( graphcount.length == 1 || graphcount.length > 2 ) {
//		jQuery("#helper-content").hide();
//		jQuery(".headbar").after('<span class="helperlink"></span>');
//		jQuery(".helperlink").click(function() {
//			if(jQuery(".helpbox").is(":visible"))
//      				 return false;
//
//			jQuery("body").prepend('<div class="eclipse"></div><div class="helpbox"></div>');
//			jQuery(".helpbox").html('<div class="helptext" >' + helphtml + '</div>');
//			jQuery(".helpbox").prepend('<span class="closebox"></span>');
//			jQuery(".closebox").click(function(){
//				jQuery(".helpbox").fadeOut();
//				jQuery(".eclipse").remove();
//			});
//		});
//	} else {
//		jQuery("#helper-content").remove();
//	}
//	/* cambio lingua */
//	jQuery(".headbar").after('<span class="langchooser"></span>');
//	jQuery(".langchooser").hover(function(){
//		jQuery("#language-chooser").stop(true,true).slideDown();
//	},function(){
//		jQuery("#language-chooser").delay(1700).slideUp();
//	});
//	/* notifiche */
//	
//	function generateNotifies() {
//			if(jQuery(".quick-notify").length > 0 && !(jQuery(".ui-pnotify").size() > 0 )) {
//				jQuery(".quick-notify").each(function() {
//					var notetitle = jQuery(this).children("h3").html();
//					var notetext = jQuery(this).children("p").html();
//
//					if(jQuery(this).hasClass('warning')) {
//						var typenotify = "error";
//						var typehide = false;
//					} else {
//						var typenotify = "notice";
//						var typehide = true;
//					}
//
//					jQuery.pnotify({
//						pnotify_addclass: 'custom',
//						pnotify_type: typenotify,
//						pnotify_title: notetitle,
//						pnotify_text: notetext,
//						pnotify_history: false,
//						pnotify_hide: typehide
//
//					});	
//				});
//			}
//	}
//	
//	function notifierAnimate(opt) {
//	  	var newOpt;
//	
//	  	if(opt == 1) {
//	  		newOpt = "0.25";
//		} else {
//			newOpt = "1";
//		}
//		jQuery(".notify-handler").animate({
//			opacity: opt
//			}, parseInt(5000 / jQuery(".quick-notify").size()), function() {
//				if(jQuery(".notify-handler").hasClass("notify-hidden")) {
//					notifierAnimate(newOpt);	
//				} else {
//					jQuery(".notify-handler").css("opacity", "1");
//				}
//			});
//
//	}
//	/* aggiungo l'icona  di gestione visibilit√† delle notifiche */
//	jQuery(".headbar").after('<span class="notify-handler"></span>');
//	
//	/* gestione dei cookie sulle notifiche */
//	if(jQuery.Storage.get("notifies") == "hidden") {
//		jQuery(".notify-handler").addClass("notify-hidden");
//		notifierAnimate("0.25");
//	} else {
//		generateNotifies();
//	}
//		
//		jQuery(".notify-handler").click(function(){
//			generateNotifies();
//		
//			if(jQuery(this).hasClass("notify-hidden")) {
//				jQuery(".ui-pnotify").fadeIn();
//				jQuery(this).removeClass("notify-hidden");
//				jQuery(".notify-handler").css("opacity", "1");
//				jQuery.Storage.remove("notifies");
//			} else {
//				jQuery(".ui-pnotify").fadeOut();
//				jQuery(this).addClass("notify-hidden");
//				notifierAnimate("0.25");
//				jQuery.Storage.set("notifies", "hidden");	
//			}
//		});
//	
//		/* chiusura notifiche forzata */
//		jQuery(".ui-pnotify-closer .ui-icon-circle-close").click(function() {
//			var url = jQuery(this).parent().parent().children(".ui-pnotify-title").children(".notifyurl-title").attr("href");
//			if(url !== undefined) {
//				if(url.indexOf("#") >= 0) { 
//					url = url.substr(0, url.indexOf("#"));
//				} else {
//					url = url + "&NotifyModify_frmAction=hide";
//				}
//				if(url.indexOf("?") >= 0) {
//					 url = url + "&NotifyModify_frmAction=hide";
//				} else {
//					url = url + "?NotifyModify_frmAction=hide";
//				}
//				url = /*"http://" + window.location.hostname + */url.replace("/admin/utility/notify/preview", "/admin/utility/notify/modify");
//			
//				jQuery.ajax({
//				   type: "GET",
//				   url: url.substr(0, url.indexOf("?")),
//				   data: url.substr(url.indexOf("?") + 1),
//				   success: function(msg){
//				     
//				   }
//				 });
//				
//			}
//		});
//	
//	/* icone quantit‡† griglie */
//	if(jQuery(".perPage")) {
//		jQuery(".selectors").text(" ");
//	}
//	
//	/* bottone aggiungi nuovo */
//	
//	jQuery(".heading").children(".addNew").text(" ");
//	
//
//	
//	/* ----- filtri forms ----- */
//	jQuery("#FormConfigModify_data").before('<a href="javascript:void(0);" class="advanced-link"></a>');
//	jQuery(".advanced").hide();
//	jQuery("#FormConfigModify_limit_by_groups_0").closest("fieldset").hide();
jQuery(function() {
//	 area chrome admin 
	jQuery("#left").height(jQuery(document).height());
	jQuery("#right").width(jQuery(document).width() - 240);
//	
//	jQuery(window).resize(function() {
//		jQuery("#left").height(jQuery(document).height());
//		jQuery("#right").width(jQuery(document).width() - 230);
//	});
	
	/* manipolazione H2 */
	
	
	/* area manage */
	if( ff.page_path.search("manage") > 0 ) {
		
		// filtri
		jQuery(".filter").hide();
		var filterButton = '<div class="filterbuttons">';

		jQuery(".filter").children(".actions").children("input").each(function() {
			filterButton = filterButton + '<a href="javascript:void(0);" class="' + jQuery(this).attr("id") + '"></a>';
		});
		filterButton = filterButton + '</div>';
		
		if(jQuery(".ui-tabs").length > 0) {
			jQuery(".heading").closest(".ui-tabs").before(filterButton);
		} else {
			jQuery(".listview").before(filterButton);
		}	
		
		
		jQuery(".filterbuttons").children(".filter").click(function(){
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
		});
		/* remove column function */
		jQuery.fn.removeCol = function(col){
		    // Make sure col has value
		    if(!col){ col = 1; }
		    jQuery('tr td:nth-child('+col+'), tr th:nth-child('+col+')', this).hide();
		    return this;
		};
		
		/* tabella totali manage */
		jQuery(".total").find("table").addClass("compactable");
		jQuery(".total").find("table").removeCol();
	/*	jQuery(".total").find("table").children("tbody").children("tr:not(:last)").each(function() {
			jQuery(this).hide();
		});
	*/
		jQuery(".total").find("table").children("tbody").children("tr:not(:last)").hide();
		
		jQuery(".total").after('<div class="toggle">+ info</div>');
		jQuery(".toggle").click(function(){
				if(jQuery(".total").find("table").hasClass("compactable")) {
					jQuery(".toggle").text("wait...");
					jQuery(".total").find("table").removeClass("compactable");
					jQuery('.total tr td:nth-child(1), .total tr th:nth-child(1)').show();
					
					jQuery(".total").find("table").children("tbody").children("tr:not(:last)").fadeIn(function() {
						jQuery(".toggle").text("- info");
					});
				} else {
					jQuery(".toggle").text("wait...");
					jQuery(".total").find("table").addClass("compactable");		
					jQuery(".total").find("table").removeCol();
					
					jQuery(".total").find("table").children("tbody").children("tr:not(:last)").fadeOut(function() {
						jQuery(".toggle").text("+ info");
					});
					
				}
		});
		
		/* tabella magazzino */
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
	
	/* fine area manage */
});