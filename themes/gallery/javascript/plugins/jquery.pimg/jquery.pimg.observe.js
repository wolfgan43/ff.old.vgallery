ff.cms.fn.pimg = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
       /*css */
   ff.pluginLoad("pimg", "/themes/library/plugins/jquery.pimg/jquery.pimg.js", function() { 
       	pimg();
	}, false);
};img