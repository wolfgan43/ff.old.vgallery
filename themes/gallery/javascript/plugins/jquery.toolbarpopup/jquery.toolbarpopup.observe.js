ff.cms.fn.toolbarpopup = function(targetid) {  
    var targetid = targetid;
    if(targetid.length > 0)
        targetid = targetid + " ";
    
    ff.injectCSS("jquery.helperborder", "/themes/library/plugins/jquery.helperborder/jquery.helperborder.css", function() {  
        ff.pluginLoad("jquery.fn.helperBorder", "/themes/library/plugins/jquery.helperborder/jquery.helperborder.js", function() {
            jQuery(targetid + 'INPUT.toolbar').each(function() {
            	var elemInput = jQuery(this);
            	var elemTarget = undefined;
            	
            	if(jQuery(this).attr("rel")) {
            		if(jQuery(this).parent().find("." + jQuery(this).attr("rel")).is("div")) {
						elemTarget = jQuery(this).nextAll("." + jQuery(this).attr("rel"));
					} else {
						elemTarget = jQuery(this).next();
					}
            	} else {
					elemTarget = jQuery(this).parent();
            	}
            	
            	if(elemTarget) {
            		if(!jQuery(elemTarget).height() && jQuery(elemTarget).children()) {
						jQuery(elemTarget).css({"overflow" : "hidden"});
            		}
		            jQuery(elemTarget).helperBorder({
		                container : "body",
				        guide : {
							enable : true,
							elemId : "#hb-outline-guide",
							exclude : [],
							showInner : true,
							border : {
								color : "rgba(0,127,255,0.65)",
								size : 1,
								style : "solid",
								shadow : 10
							},
							innerCallback : undefined,
				        },
				        guideSelected : {
							enable : true,
							elemId : "#hb-outline-selected",
							exclude : [],
				        	timer : 800,
							useDrag : false,
							useResize : false,
							showInner : true,
							border : {
								color : "red",
								size : 1,
								style : "solid",
								shadow : 10
							},
							innerCallback : function(elem, toolbarContainer) {
			                    var source = jQuery(elemInput);
			                    var link = jQuery(source).attr("value"); 
			                    
			                    var elemToolbar = jQuery(source).next();
			                    
			                    if(jQuery(source).hasClass("loaded")) {
			                        if(jQuery(this).is(":visible") && jQuery(elemToolbar).hasClass("toolbar") && jQuery(elemToolbar).text()) {
										setToolbarPosition(toolbarContainer, elemToolbar, elemTarget);
			                        	
			                            jQuery(toolbarContainer).html(jQuery(elemToolbar).outerHTML()).children().show();
			                        }
			                    } else {
			                        jQuery(source).addClass("loaded");  
			                        jQuery.ajax({
			                            async: true,    
			                            type: "POST",
			                            url: link.substring(0, link.indexOf("?")), 
			                            data: link.substring(link.indexOf("?") + 1),
			                            cache: true, 
			                            success: function(item) {
			                                jQuery(source).after(item);
			                                jQuery(source).next().hide();
			                                 
			                                 var elemToolbar = jQuery(source).next();
			                                 
		                                	jQuery(elemToolbar).find("a.admin-link").each(function() {
												if(jQuery(this).attr("title").length > 0) {
			                                        jQuery(this).text(jQuery(this).attr("title"));
			                                        jQuery(this).removeClass("admin-link").addClass("admin-link-title");
			                                    }
											});

											setToolbarPosition(toolbarContainer, elemToolbar, elemTarget);
			                                
											if(jQuery(elemToolbar).text()) {
			                                	jQuery(toolbarContainer).html(jQuery(elemToolbar).outerHTML()).children().show();
											}
			                            }
			                        });
			                    }

			                    
			                }
							
						}
		                
		            });
				}
	        });
        });
    });
    
    
    var setToolbarPosition = function(toolbarContainer, elemToolbar, elemTarget) {
		return;
		jQuery(elemToolbar).removeAttr("hb-right-top");
		jQuery(elemToolbar).removeAttr("hb-right-bottom");

		if(jQuery("#hb-outline-selected").width() <= jQuery(elemToolbar).width() + (jQuery(elemToolbar).find("a.admin-link-title").length * 20)) { 
		    if(parseInt(jQuery("#hb-outline-selected").css("top").replace("px", "")) > parseInt(jQuery(toolbarContainer).parent().css("min-height").replace("px", ""))) {
		        jQuery(toolbarContainer).addClass("hb-right-top");
			} else {
				jQuery(toolbarContainer).addClass("hb-right-bottom");
			}
		    
		    /*jQuery(elemToolbar).find("a.admin-link-title").text(""); */
			/*jQuery(elemToolbar).find("a.admin-link-title").removeClass("admin-link-title").addClass("admin-link"); */
		}
		
    }
    /*css */
};