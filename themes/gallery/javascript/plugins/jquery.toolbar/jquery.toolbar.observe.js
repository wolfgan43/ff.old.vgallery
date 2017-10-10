ff.cms.fn.toolbar = function(targetid) {  
	var targetid = targetid;
	var objTarget = jQuery(targetid + '.block[data-admin]');
	if(targetid && !objTarget.length)
		objTarget = jQuery(targetid + ' .block[data-admin]');

    if(objTarget.length
    	//|| jQuery("INPUT.toolbar[rel='" + targetid + "']").length
    ) {
    	/*jQuery(document).on("mouseenter", ".vg-toolbar",  function() {
			jQuery(".vg-layout-actions", this).slideDown();
		});
    	jQuery(document).on("mouseleave", ".vg-toolbar",  function() {
			jQuery(".vg-layout-actions", this).hide();
		});*/

	    ff.injectCSS("jquery.helperborder", "/themes/library/plugins/jquery.helperborder/jquery.helperborder.css", function() {  
	        ff.pluginLoad("jquery.fn.helperBorder", "/themes/library/plugins/jquery.helperborder/jquery.helperborder.js", function() {
	    		//jQuery(targetid + " INPUT.toolbar, INPUT.toolbar[rel='" + targetid + "']").each(function() {
	    		objTarget.each(function() {
            		var elemInput = jQuery(this);
            		var elemTarget = undefined;
            		
            		if(jQuery(this).attr("rel")) {
            			if(jQuery(this).find("." + jQuery(this).attr("rel")).is("div")) {
							elemTarget = jQuery(this).find("." + jQuery(this).attr("rel"));
						} else {
							elemTarget = jQuery(this).children(":first");
						}
            		} else {
						elemTarget = jQuery(this);
            		}

            		if(elemTarget) {
            			if(!jQuery(elemTarget).height() && !jQuery(elemTarget).children()) {
							var ctxW = jQuery(elemTarget).width() || 100;
							var ctxH = jQuery(elemTarget).height() || 100;
							
							jQuery(elemTarget).css({"overflow" : "hidden", "width" : ctxW + "px", "height" : ctxH + "px"});
							if(document.getCSSCanvasContext) {
								jQuery(elemTarget).css({"background": "-webkit-canvas(squares)"});
								
								var ctx = document.getCSSCanvasContext("2d", "squares", ctxW, ctxH);
							} else {
								if(!jQuery("#ctx-empty").length) {
									jQuery("body").append('<canvas id="ctx-empty" width="' + ctxW + '" height="' + ctxH + '"></canvas>');
								}
								jQuery(elemTarget).css({"background": "-moz-element(#ctx-empty)"});

								var ctx = document.getElementById("ctx-empty").getContext("2d");
							}
							
							ctx.rect(0,0,ctxW,ctxH);
							ctx.strokeStyle = '#d3d3d3';
							ctx.stroke(); 
							ctx.moveTo(0,0);
							ctx.lineTo(ctxW,ctxH);
							ctx.strokeStyle = '#d3d3d3';
							ctx.stroke();
							ctx.beginPath();
							ctx.moveTo(ctxW,0);
							ctx.lineTo(0 ,ctxH);
							ctx.strokeStyle = '#d3d3d3';
							ctx.stroke();						
            			}

			            jQuery(elemTarget).helperBorder({
			                container : "body",
					        guide : {
								enable : true,
								elemId : "#hb-outline-guide",
								exclude : [],
								showInner : true,
								innerCallback : undefined,
								margin : 6
					        },
					        guideSelected : {
								enable : true,
								elemId : "#hb-outline-selected",
								exclude : [],
				        		timer : 800,
								useDrag : false,
								useResize : false,
								showInner : true,
								margin : 3,
								innerCallback : function(elem, toolbarContainer) {
				                    var source = jQuery(elemInput);
				                    var link = jQuery(source).data("admin"); 
				                    var elemToolbar = jQuery(source).next();
				                    
				                    if(jQuery(source).hasClass("loaded")) {
				                        if(jQuery(elemToolbar).hasClass("vg-toolbar") && jQuery(elemToolbar).text()) {
				                            jQuery(toolbarContainer).html(jQuery(elemToolbar).outerHTML()).children().show();
                                            
                                            var toolbarContainerWidth = jQuery(toolbarContainer).innerWidth();
                                            jQuery("a", toolbarContainer).each(function() {
                                                if(jQuery(this).width() > 0)
                                                    toolbarContainerWidth =  toolbarContainerWidth - jQuery(this).outerWidth(true);
                                                
                                                if(toolbarContainerWidth <= 0) 
                                                    return;
                                            });

                                            if(toolbarContainerWidth <= 0) {      
                                                //jQuery(".vg-layout-title", toolbarContainer).text("");
                                                jQuery(toolbarContainer).addClass("vg-toggle");  
                                            }                                            
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
			                            		if(item) {
					                                jQuery(source).after(item);
                                                    
                                                    var elemToolbar = jQuery(source).next();

					                                elemToolbar.hide();

			                                		jQuery(toolbarContainer).html(jQuery(elemToolbar).outerHTML()).children().show();

                                                    var toolbarContainerWidth = jQuery(toolbarContainer).innerWidth();
                                                    jQuery("a", toolbarContainer).each(function() {
                                                        if(jQuery(this).width() > 0)
                                                            toolbarContainerWidth =  toolbarContainerWidth - jQuery(this).outerWidth(true);
                                                
                                                        if(toolbarContainerWidth <= 0) 
                                                            return;
                                                    });

                                                    if(toolbarContainerWidth <= 0) {      
                                                       // jQuery(".vg-layout-title", toolbarContainer).text("");
                                                        jQuery(toolbarContainer).addClass("vg-toggle");  
                                                    }
												} else {
													jQuery(toolbarContainer).html("");
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
	}

    /*css */
};