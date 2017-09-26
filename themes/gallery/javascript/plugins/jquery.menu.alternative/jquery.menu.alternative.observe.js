ff.cms.fn.menualternative = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	/*css*/
	var timeout     = 500;
	var closetimer	= 500;
	var menuitem    = 0;
	var that = { /* publics */
		"menu_open" : function() {	
			that.menu_canceltimer();
			that.menu_close();
			menuitem = jQuery(this).find('ul').eq(0).css('visibility', 'visible');
		},
		"menu_close" : function() {	
			if(menuitem) menuitem.css('visibility', 'hidden');
		},
		"menu_timer" : function() {
			closetimer = window.setTimeout(that.menu_close, timeout);
		},
		"menu_canceltimer" : function() {	
			if(closetimer) {
				window.clearTimeout(closetimer);
				closetimer = null;
			}
		}
	};

	jQuery(targetid + ".menualternative > li").bind('mouseover', that.menu_open);
	jQuery(targetid + ".menualternative > li").bind('mouseout',  that.menu_timer);

	return that;
};
