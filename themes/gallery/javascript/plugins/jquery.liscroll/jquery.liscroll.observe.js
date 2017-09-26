ff.cms.fn.liscroll = function(targetid) {
    var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
		
    /*css */
    ff.load("jquery.plugins.liscroll", function() {    
        jQuery(targetid + ".liscroll").closest("ul").liScroll({
            travelocity: 0.07
        }); 
    });
};
