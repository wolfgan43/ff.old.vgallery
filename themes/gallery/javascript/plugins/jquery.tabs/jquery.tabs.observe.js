ff.cms.fn.jqueryui_tabs = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
	
	ff.pluginLoad("jquery-ui", "/themes/library/jquery-ui/jquery-ui.js", function() {
	    jQuery(targetid + '.jqueryui-tabs').each(function() {
	        jQuery(this).tabs({ fx: { opacity: 'toggle' } });
	    });
	}, true);
};