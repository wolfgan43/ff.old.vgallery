ff.cms.fn.slidingpanel = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	/*css*/
	jQuery(targetid + ".slidingpanel").each(function() {
		jQuery(this).parent().append('<div class="slidingtoggle"><a href="#"><img src="' + ff.base_path + '/themes/gallery/javascript/plugin/jquery.slidingpanel/toggle.png"></a></div>');
        jQuery(this).next().click(function() {
            if(jQuery(this).prev().hasClass("visible")) {
                jQuery(this).prev().slideUp("slow");
                jQuery(this).prev().removeClass("visible");
            } else {
                jQuery(this).prev().slideDown("slow");
                jQuery(this).prev().addClass("visible");
            }
        });
	});
};