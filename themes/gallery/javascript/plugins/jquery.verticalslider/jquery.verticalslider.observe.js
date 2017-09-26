ff.cms.fn.verticalslider = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	/*css*/
    jQuery(targetid + ".verticalslider").each(function(){
        jQuery(this).parent().append('<div class="verticalslidingtoggle"><a href="#"><img src="' + ff.base_path + '/themes/gallery/javascript/plugin/jquery.verticalslider/toggle.png"></a></div>');
        jQuery(this).next().click(function(){
            jQuery(this).prev().toggle("fast");
            jQuery(this).toggleClass("active");
            return false;
        });
    });
};