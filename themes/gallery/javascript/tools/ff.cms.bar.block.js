ff.cms.fn.toolbar = function(targetid) {  
	var objTarget = jQuery(targetid + ' .block[id!=""]');		
	var cache = {};

	ff.load("jquery.plugins.helperborder", function() {
	    objTarget.each(function() {
			var $source = jQuery(this);
            var $target = $source;
            var blockID = $source.attr("id");

            if($source.attr("rel")) {
            	if($source.find("." + $source.attr("rel")).is("div")) {
					$target = $source.find("." + $source.attr("rel"));
				} else {
					$target = $source.children(":first");
				}
            }

            if(!$target.height() && !$target.children()) {
				var ctxW = $target.width() || 100;
				var ctxH = $target.height() || 100;
				
				$target.css({"overflow" : "hidden", "width" : ctxW + "px", "height" : ctxH + "px"});
				if(document.getCSSCanvasContext) {
					$target.css({"background": "-webkit-canvas(squares)"});
					
					var ctx = document.getCSSCanvasContext("2d", "squares", ctxW, ctxH);
				} else {
					if(!jQuery("#ctx-empty").length) {
						jQuery("body").append('<canvas id="ctx-empty" width="' + ctxW + '" height="' + ctxH + '"></canvas>');
					}
					$target.css({"background": "-moz-element(#ctx-empty)"});

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

			$target.helperBorder({
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
				        var link = $source.data("admin"); 
				        if($source.hasClass("draft") 
				        	|| $source.hasClass("file") 
				        )
				            link = ff.site_path + "/admin/block/" + blockID;
						
						//jQuery(toolbarContainer).html("");
				        if(cache[blockID] !== undefined) {
				            jQuery(toolbarContainer).html(cache[blockID]).children().show();
				        } else {
				            cache[blockID] = true;
				            jQuery.get(link, function(block) {
								cache[blockID] = block;
			                    jQuery(toolbarContainer).html(cache[blockID]).children().show();
				            });
				        }
				    }
				}
			});
			
			jQuery(".admin-bar", $target).hover(function() {
				var $item = jQuery(this);
				var link = $item.data("admin");
				if(cache[blockID + link] !== undefined) {
					$item.prepend('<div class="vg-toolbar">' + cache[blockID + link] + '</div>');
				} else {
					jQuery.get(jQuery(this).data("admin"), function(item) {
						cache[blockID + link] = item;
						$item.prepend('<div class="vg-toolbar">' + cache[blockID + link] + '</div>');
					});
				}
			}, function() {
				var $item = jQuery(this);

				jQuery(".vg-toolbar", $item).remove();
			});
		});
	});
};