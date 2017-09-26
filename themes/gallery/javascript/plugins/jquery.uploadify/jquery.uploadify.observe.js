ff.cms.fn.uploadify = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	/*css*/
	ff.load("jquery.plugins.uploadify", function() {
	    jQuery(targetid + ".uploadify").uploadify({
	        'uploader'       : ff.site_path + '/themes/library/plugins/jquery.uploadify/uploadify.swf',
	        'script'         : ff.site_path + '/themes/library/plugins/jquery.uploadify/uploadify.php' +  jQuery("#type_upload").attr("value"),
	        'cancelImg'      : ff.base_path + '/themes/library/plugins/jquery.uploadify/cancel.png',
	        'folder'         : jQuery("#path_upload").attr("value"),
		    'buttonText'	 : 'Sfoglia', 
	        /*'buttonImg'      : ff.site_path + '/themes/library/plugins/jquery.uploadify/browse.png',*/
	        'auto'           : false,
	        'multi'          : true, 
	        'sizeLimit'      : jQuery("#max_upload").attr("value")
	    });
	});
};