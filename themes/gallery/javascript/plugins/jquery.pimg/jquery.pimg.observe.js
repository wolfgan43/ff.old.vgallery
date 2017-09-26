ff.cms.fn.pimg = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
       /*css */
   ff.load("jquery.plugins.pimg", function() { 
       	pimg();
	});
};img