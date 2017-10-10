ff.cms.fn.liscroll = function(targetid) {
    var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
		
    /*css */
    ff.pluginLoad("jquery.fn.liScroll", "/themes/library/plugins/jquery.liscroll/jquery.liscroll.js", function() {    
        jQuery(targetid + ".liscroll").closest("ul").liScroll({
            travelocity: 0.07
        }); 
    }, true);
};
