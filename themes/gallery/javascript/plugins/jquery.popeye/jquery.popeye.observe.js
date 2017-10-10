ff.cms.fn.popeye = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	/*css */
	ff.pluginLoad("jquery.fn.popeye", "/themes/library/plugins/jquery.popeye/jquery.popeye.js", function() {	
		var options = {
		            caption:    false, /*The visibility of the navigation. Can be 'hover' (show on mouseover) or 'permanent' */
		            navigation: 'permanent', /*The visibility of the navigation. Can be 'hover' (show on mouseover) or 'permanent' or false (don't show caption) */
		            direction:  'left', /*direction that popeye-box opens, can be 'left' or 'right' */
					"z-index": 10000, /*z-index of the expanded popeye-box. Enter a z-index that works well with your site and doesn't overlay your site's navigational elements like dropdowns. */
		       		duration: 240, /*duration of transitional effect when enlarging or closing the box */
					opacity: 0.8 /*opacity of navigational overlay */
					/* easing: 'swing' //easing type, can be 'swing', 'linear' or any of jQuery Easing Plugin types (Plugin required)*/
		};
		jQuery(targetid + ".popeye").popeye(options);
	}, true);
};