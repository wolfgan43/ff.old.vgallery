if (!ff.cms) ff.cms = {};
ff.cms.search = (function () {
	var buffer = {};
	var displayBlock = function(container, target, displayNoSearch) {
		var search = {};
		var countSearch = 0;
		var blockContainer = jQuery(container).closest(".block");
		var linkHistory = window.location.href;

		if(!target)
		    target = container.attr("id") + "SB";

		if(!jQuery("#" + target).length) { 
		    jQuery('<div class="block" id="' + target + '" />').insertAfter(blockContainer);
		}

		jQuery("INPUT, SELECT, TEXTAREA", container).each(function() {
			var itemId = jQuery(this).attr("ID");
			var key = ff.slug(itemId.substring(itemId.lastIndexOf("_")));
			var value = ff.slug(jQuery(this).val());
			search[key] = value;
			if(value)
				countSearch++;
				
			linkHistory = ff.cms.updateUriParams(key, value, linkHistory);
		});
		
		if(countSearch) {
			if(blockContainer.nextAll("#" + target).length) {
				blockContainer.nextUntil("#" + target).each(function() {
					jQuery(this).slideUp().addClass(target + "-hide");		    		
				});		
			} else if(blockContainer.prevAll("#" + target).length) {
				blockContainer.prevUntil("#" + target).each(function() {
					jQuery(this).slideUp().addClass(target + "-hide");		    		
				});		
			}
			return {
				"target" : target,
				"search" : search
			};
		} else if(!displayNoSearch) {
			jQuery("#" + target).html("");
			jQuery("." + target + "-hide").slideDown().removeClass(target + "-hide");

			linkHistory = ff.cms.updateUriParams("page", "", linkHistory);

			history.replaceState(null, null, linkHistory);
			
			return false;
		}
	};
	var that = { /* publics*/
		__init : false
		, "init" : function(params) { 
			that.__init = true;
		}
		, "term" : function(elem, event) {
			if(event.keyCode == 13 && jQuery(elem).val()) {
				window.location.href = ff.site_path + "/search/" + ff.slug(jQuery(elem).val());
			}
			return false;
		
		}
		, "block" : function(elem, url, target) {
			function restoreAction(elem, target) {
	            jQuery("#" + res["target"]).removeClass("loading");
		        jQuery(elem).css({"opacity": "", "pointer-events" : ""});
		        jQuery("i", elem).remove();			
			};

			var res = displayBlock(jQuery(elem).closest("*[data-advsrc-target]"), target);
		    if(res) {
		    	jQuery("#" + res["target"]).addClass("loading"); 
		        ff.cms.getBlock(res["target"], {
		        	"url" : url, 
		        	"search" : res["search"], 
		        	"page" : 1, 
		        	"jumpUI": false
		        } , function() {  
					restoreAction(elem, res["target"]);
		        });
		    } else {
		    	restoreAction(elem, res["target"]);
		    }

			return false;
		}
	};
	jQuery(function() {
		jQuery("*[data-advsrc-target]").each(function() {
			displayBlock(this, jQuery(this).attr("data-advsrc-target"), true);
		});
	});
	return that;
})();