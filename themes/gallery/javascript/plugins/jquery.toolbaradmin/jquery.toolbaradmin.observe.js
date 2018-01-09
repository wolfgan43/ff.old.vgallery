ff.cms.fn.toolbaradmin = function(targetid) {
	var displayMode = "";
	var targetid = targetid;
	if(targetid != "#adminPanel")
		return;

	var zIndex = 90;
	$("*").each(function() {
		var current = parseInt($(this).css("z-index"), 10);
		if(current < 2000 && zIndex < current) zIndex = current;
	});			
    zIndex = zIndex + 10;
	jQuery("#adminPanel").css("z-index", zIndex);

	var toolbarToggleTimer = "";
	var toolbarToggle = function(display, noAnimate) {
		//toolbarToggleTimer = "";
		if(document.cookie.indexOf("cms-toolbar-fixed") < 0) {
			if(display === undefined)
				display = !jQuery(targetid + ".toolbaradmin > ul").is(":visible");
			
			jQuery(targetid + ".toolbaradmin > ul:not(submenu) > li > ul.submenu").hide();
			if(display) {
				jQuery(targetid + ".toolbaradmin > ul").show();
				jQuery(targetid + ".toolbaradmin > .right").show();
				jQuery(targetid + ".toolbaradmin").css({
					"height": "32px"
					, "background" : "#222"
					, "border" : ""
				}); 
			} else {
				if(noAnimate) {
					jQuery(targetid + " > ul").hide();
					jQuery(targetid + " > .right ").hide();
					jQuery(targetid + ".toolbaradmin").css({
						"height" : "7px"
						, "background" : "transparent"
						, "border-top" : "1px solid rgba(0,127,255,0.80)"
					});			
				} else {
					jQuery(targetid + ".toolbaradmin").animate({
						"height" : "0"
					},300, function() {
						jQuery(targetid + " > ul").hide();
						jQuery(targetid + " > .right ").hide();
						jQuery(this).css({
							"height" : "7px"
							, "background" : "transparent"
							, "border-top" : "1px solid rgba(0,127,255,0.80)"
						});			
					});
				}
			}
		}
	}
	if(ff.cms.editor)
		ff.cms.editor.init();

	if(ff.cms.seo) {
		ff.cms.seo.init();
	}
	/*jQuery(targetid + ".toolbaradmin").children(".first").append(''); */
	
	jQuery("#adminPanel").draggable();

	if(document.cookie.indexOf("cms-toolbar-fixed") < 0) {
		toolbarToggle(false, true);
	} else {
		jQuery(targetid + ".toolbaradmin .hide-toggle").addClass("pressed");
	}

	//if(document.cookie.indexOf("toolbarclosed") >= 0) {
		//jQuery(targetid + ".toolbaradmin .hide-toggle").removeClass("pressed");
		//toolbarToggle(true);
		//displayMode = "show";
	//}
	
	jQuery(targetid + ".toolbaradmin .hide-toggle").click(function() {
		var elem = this;
		ff.pluginLoad("jquery.fn.cookie", "/themes/library/plugins/jquery.cookie/jquery.cookie.js", function() {
			if(document.cookie.indexOf("cms-toolbar-fixed") >= 0) {
				jQuery(elem).removeClass("pressed");
				jQuery.cookie("cms-toolbar-fixed", null);
			} else {
				jQuery(elem).addClass("pressed");
				jQuery.cookie("cms-toolbar-fixed", true);
			}
		});
/*
		if(jQuery(targetid + ".toolbaradmin > ul").is(":visible")) {
			ff.pluginLoad("jquery.fn.cookie", "/themes/library/plugins/jquery.cookie/jquery.cookie.js", function() {
				jQuery.cookie("toolbarclosed", true);
			});
		} else {
			ff.pluginLoad("jquery.fn.cookie", "/themes/library/plugins/jquery.cookie/jquery.cookie.js", function() {
				jQuery.cookie("toolbarclosed", null);
			});
		}
*/
		//toolbarToggle();
	});

	jQuery(targetid + ".toolbaradmin").hover(
	  function() {
	  	if(toolbarToggleTimer) {
			clearTimeout(toolbarToggleTimer);
			toolbarToggleTimer = "";
		}
		
		toolbarToggle(true);
	  }, function() {
	  	toolbarToggleTimer = setTimeout(function() {toolbarToggle(false); }, 1000);
	  }
	);


	jQuery(targetid + ".toolbaradmin a:not(.stay-open)").on("click", function() {
		jQuery(targetid + ".toolbaradmin .submenu").hide();
	});

	ff.pluginLoad("jquery.fn.hoverIntent", "/themes/library/plugins/jquery.hoverintent/jquery.hoverintent.js", function() {
		/*jQuery(targetid + ".toolbaradmin").hoverIntent({
			sensitivity: 3,
			over :function(e){
				if(toolbarToggleTimer) {
					clearTimeout(toolbarToggleTimer);
					toolbarToggleTimer = "";
				}			
				if(document.cookie.indexOf("toolbarclosed") >= 0 
					&& !jQuery(targetid + ".toolbaradmin > ul").is(":visible")
				) {
					toolbarToggle();
				}
			}, 
			out : function(e) {
				if(document.cookie.indexOf("toolbarclosed") >= 0 
					&& jQuery(targetid + ".toolbaradmin > ul").is(":visible")
					&& !jQuery(targetid + ".toolbaradmin > ul:not(submenu) > li > ul.submenu").is(":visible")
				) {
					toolbarToggleTimer = setTimeout(toolbarToggle, 1000);
				}
			}
		});	*/

		jQuery(targetid + ".toolbaradmin > ul:not(submenu) > li").hoverIntent({
			sensitivity: 3,
			over : function(){
				if(!jQuery(this).children(".submenu").is(":visible")) {
					jQuery(this).closest("ul").find(".submenu").hide();
					jQuery(this).find("li.layout").each(function() {
						var layoutRel = [];
						if(jQuery("#L" + jQuery(this).attr("rel")).length) {
							layoutRel.push("L" + jQuery(this).attr("rel"));
						} 
						if(jQuery("#L" + jQuery(this).attr("rel") + "T").length) {
							layoutRel.push("L" + jQuery(this).attr("rel") + "T");
						} 
						if(jQuery("#L" + jQuery(this).attr("rel") + "V").length) {
							layoutRel.push("L" + jQuery(this).attr("rel") + "V");
						} 
						
						if(layoutRel.length) {
							jQuery(this).attr("data-rel", layoutRel.join(","));
							jQuery(this).removeClass("notvisible");
							jQuery(this).removeClass("hidden");
						} else if(0 && !jQuery(this).hasClass("ui-state-disabled")) {
							jQuery(this).removeAttr("data-rel");
							jQuery(this).addClass("ui-state-disabled");
							jQuery(this).addClass("notvisible");
							jQuery(this).addClass("hidden");
						}			
					});
					jQuery(this).children(".submenu").stop(true,true).slideDown();
				}
			}, 
			out: function() {
			//jQuery(targetid + ".toolbaradmin .submenu").hide();
				/*jQuery(this).children(".submenu").hide(); */
			}
		});
	});
   /*	$("body").bind("click", function(e) { 
   		if(!jQuery(e.target).closest(targetid + ".toolbaradmin").length) {
	   		jQuery(targetid + ".toolbaradmin > ul:not(submenu) > li > ul.submenu").hide();
	   		if(jQuery(targetid + ".toolbaradmin > ul").is(":visible") && document.cookie.indexOf("toolbarclosed") >= 0)
	   			toolbarToggle();
		}
	});  */

	// Da Approfondire
	/*if(jQuery(targetid + ".toolbaradmin ul.first li.thumb-section").length > 1) {
		jQuery(targetid + ".toolbaradmin ul.first").sortable({
			items: "li.thumb-section, li.vg-sep"
			, placeholder: "ui-state-highlight"
			, start: function(event, ui) {
				jQuery("li.vg-sep", this).width("4");	
				jQuery("li.vg-sep", this).css("margin", "0 4px");
			}
			, change : function(event, ui) {
			}
			, stop : function(event, ui) {
				jQuery("li.vg-sep", this).width("1");
				jQuery("li.vg-sep", this).css("margin", "0");	
				
				//jQuery.post("/srv/sort/layout?location=" + location + "&positions=" + positions.join(), function(data) {
				//});
			}
		});
	}	*/
	
	
	//console.log(targetid + ".toolbaradmin li.thumb-section ul.submenu");
	jQuery(targetid + ".toolbaradmin li.thumb-section ul.submenu").each(function() {
		var that = this;
		jQuery(this).children("li").each(function() {
			if(!jQuery(this).hasClass("notvisible")) {
				var layoutRel = [];
				if(jQuery("#L" + jQuery(this).attr("rel")).length) {
					layoutRel.push("L" + jQuery(this).attr("rel"));
				} 
				if(jQuery("#L" + jQuery(this).attr("rel") + "T").length) {
					layoutRel.push("L" + jQuery(this).attr("rel") + "T");
				} 
				if(jQuery("#L" + jQuery(this).attr("rel") + "V").length) {
					layoutRel.push("L" + jQuery(this).attr("rel") + "V");
				} 
				
 /*				console.log(layoutRel); */
				if(layoutRel.length) {
					jQuery(this).attr("data-rel", layoutRel.join(","));
				} else if(0 && !jQuery(this).hasClass("ui-state-disabled")) {
					jQuery(this).addClass("ui-state-disabled");
				}
			}
		});

		if(jQuery(this).children("li:not(.ui-state-disabled)").length > 1) {
			jQuery(this).sortable({
				items: "li:not(.ui-state-disabled)"
				, placeholder: "ui-state-highlight"
				, start: function(event, ui) {
					if(jQuery(ui.item).attr("data-rel")) {
						jQuery("#" + jQuery(ui.item).attr("data-rel").replace(",", ",#")).helperBorder("borderGuide", jQuery(".ui-state-highlight").css("background-color"));	
						jQuery("#" + jQuery(ui.item).attr("data-rel").replace(",", ",#")).css({
							"opacity": 0.5
							/*, "-webkit-transition":"all 0.3s"
							, "transition":"all 0.3s"
							, "transform": "scale(1.5)"
							, "-webkit-transform": "scale(1.5)"*/
						});

					}
					 ui.item.data('start', ui.item.index());
				}
				, change : function(event, ui) {

					var swapElem = undefined;
					if(ui.placeholder.index() > ui.item.data('start')) {
						swapElem = ui.placeholder.prevAll(":not(.ui-state-disabled)");	
						jQuery(jQuery("#" + ui.item.data("rel").replace(",", ",#"))).insertAfter(jQuery("#" + swapElem.attr("data-rel").replace(",", ",#")));
					} else {
						swapElem = ui.placeholder.nextAll(":not(.ui-state-disabled)");	
						jQuery(jQuery("#" + ui.item.data("rel").replace(",", ",#"))).insertBefore(jQuery("#" + swapElem.attr("data-rel").replace(",", ",#")));
					}
					var dataRelOffset = jQuery("#" + ui.item.data("rel").replace(",", ",#")).offset();

					jQuery(document).scrollTop(dataRelOffset.top - (jQuery(window).height() * 0.20));
					jQuery("#" + ui.item.data("rel").replace(",", ",#")).helperBorder("showGuide");
					
				 	ui.item.data('start', ui.placeholder.index());
				}
				, stop : function(event, ui) {
					if(jQuery(ui.item).attr("data-rel")) {
						jQuery("#" + jQuery(ui.item).attr("data-rel").replace(",", ",#")).helperBorder("borderGuide");
						jQuery("#" + jQuery(ui.item).attr("data-rel").replace(",", ",#")).css({
							"opacity" : 1
							/*, "-webkit-transition":"all 0.3s"
							, "transition":"all 0.3s"
							, "transform": "scale(1)"
							, "-webkit-transform": "scale(1)"*/
						});						
					}

					var location = jQuery(ui.item).closest("li.thumb-section").attr("rel");
					var positions = [];
					jQuery(ui.item).closest("ul").children("li.layout").each(function() {
						positions.push(jQuery(this).attr("rel"));
					});
					
					jQuery.post(ff.site_path + "/srv/sort/layout?location=" + location + "&positions=" + positions.join(), function(data) {
					});
				}
			});
		}
	});
	$(targetid + ".toolbaradmin ul.submenu" ).disableSelection();
	jQuery(targetid + ".toolbaradmin li.thumb-section .display-layout").each(function() {
		var layoutLi = jQuery(this).closest("ul").find("li.layout");
		if(!(layoutLi.length && layoutLi.hasClass("hidden"))) {
			jQuery(this).hide();
		}
	});
	jQuery(targetid + ".toolbaradmin li.thumb-section .display-layout").click(function() {
		var oldClass = jQuery(this).attr("class").replace("display-layout stay-open", "");
		var newClass = jQuery(this).attr("rel");

		jQuery(this).attr("class", "display-layout stay-open " + newClass).attr("rel", oldClass);
		
		if(jQuery(this).closest("li").hasClass("hide-layout")) {
			jQuery(this).closest("ul").find("li.layout").removeClass("hidden");
			jQuery(this).closest("li").removeClass("hide-layout");
		} else {
			jQuery(this).closest("ul").find("li.layout.notvisible").addClass("hidden");
			jQuery(this).closest("li").addClass("hide-layout");
		}
		return false;
	});

	jQuery(targetid + ".toolbaradmin li.layout").hover(
	function(){
		if(jQuery(this).attr("data-rel")) {
			jQuery("#" + jQuery(this).attr("data-rel").replace(",", ",#")).helperBorder("showGuide");
		}
	}, function() {
		if(jQuery(this).attr("data-rel")) {
			jQuery("#" + jQuery(this).attr("data-rel").replace(",", ",#")).helperBorder("hideGuide"); 
		}
	});
	
	jQuery(targetid + ".toolbaradmin li.layout a.block-info").click(function() {
		var oldClass = jQuery(this).attr("class").replace("block-info stay-open", "");
		var newClass = jQuery(this).attr("rel");

		jQuery(this).attr("class", "block-info stay-open " + newClass).attr("rel", oldClass);
			
		if(jQuery(this).closest("li").find("table").hasClass("info-hide")) {
			jQuery(this).closest("ul").find("table:not('.info-hide')").parent().children("a.block-info").click();
			jQuery(this).closest("li").find("table").removeClass("info-hide");
			if(jQuery(this).closest("li").attr("data-rel")) {
				var dataRelOffset = jQuery("#" + jQuery(this).closest("li").attr("data-rel").replace(",", ",#")).offset();
				
				jQuery("#" + jQuery(this).closest("li").attr("data-rel").replace(",", ",#")).helperBorder("showGuideSelected");
				jQuery(this).parent().find("SPAN.block-top").text(Math.round(dataRelOffset.top));
				jQuery(this).parent().find("SPAN.block-left").text(Math.round(dataRelOffset.left));
				jQuery(this).parent().find("SPAN.block-width").text(Math.round(jQuery("#" + jQuery(this).closest("li").attr("data-rel").replace(",", ",#")).width()));
				jQuery(this).parent().find("SPAN.block-height").text(Math.round(jQuery("#" + jQuery(this).closest("li").attr("data-rel").replace(",", ",#")).height()));
				
				
				jQuery(document).scrollTop(dataRelOffset.top - (jQuery(window).height() * 0.20));
				
			} else {
				jQuery(document).helperBorder("hide");
			}
			
			if(jQuery(this).closest("ul.submenu").hasClass("ui-sortable"))
				jQuery(this).closest("ul.submenu").sortable( "disable" );
		} else {
			jQuery(this).closest("li").find("table").addClass("info-hide");
			if(jQuery(this).closest("li").attr("data-rel")) {
				jQuery("#" + jQuery(this).closest("li").attr("data-rel").replace(",", ",#")).helperBorder("hideGuideSelected");
			}

			if(jQuery(this).closest("ul.submenu").hasClass("ui-sortable"))
				jQuery(this).closest("ul.submenu").sortable( "enable" );
		}
		return false;
	});
	return displayMode;
};
