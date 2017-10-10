ff.cms.fn.jqueryui_accordion = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
	
	ff.pluginLoad("jquery.ui", "/themes/library/jquery.ui/jquery.ui.js", function() {
	    jQuery(targetid + '.accordion > li').each(function() {
	        jQuery(this).prepend('<h3></h3>');
	        jQuery('#' + this.id + ' > *:not(h3, ul, div.desc)').appendTo('#' + this.id + ' > h3');
	    });

	    jQuery(targetid + '.accordion').each(function() {
	        jQuery(this).accordion({ 
	                    collapsible: true
	                    , header: '> li:has(ul, div.desc) > :first-child,> :not(li):even'
	                    , autoHeight: false
	                    , event: 'click' 
	                    , navigation: false
	                    , active : function(count, elem) { 
                    		if(jQuery(elem).parent().hasClass("current")) { 
                    			return true;
                    		} else {
                    			return false;
                    		} 
	                    }
	        });
	    });
	}, true);
};