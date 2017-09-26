ff.cms.fn.lightbox = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
		
	/*css*/
	ff.load("jquery.plugins.lightbox", function() {	
		jQuery(targetid + "a.lightbox2").lightBox({
			featBrowser: true, /* set it to true or false to choose to auto-adjust the maximum size to the browser*/
			breathingSize: 10 /* control the minimum space around the image box	*/
		});
	}, true);
};
