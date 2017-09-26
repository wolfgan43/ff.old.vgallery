ff.cms.fn.freewall = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
	
	var wall = {};
	ff.ajax.addEvent({
		"event_name"	: "onEmptyQueue",
		"func_name"		: function (data) {
			wall.fitWidth();
		}
	}); 
	jQuery(targetid + '.freewall').closest("vgc,vgallery_item").wrapAll("<div class='free-wall'>");
	
	wall = new freewall(jQuery(targetid + '.free-wall'));
	
	wall.reset({  
		selector: jQuery(targetid + '.free-wall > DIV'), 
		cellW: 150, 
		cellH: "auto",
		animate: false,
		onResize: function() {
			wall.fitWidth();
		}
	});
}