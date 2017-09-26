ff.cms.fn.simplemenuclick = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
	targetid = targetid + " ";
	jQuery(targetid + 'ul.simplemenuclick > li ul').parent().prepend('<a href="javascript:void(0);" class="icon"></a>');
	jQuery(targetid + 'ul.simplemenuclick > li ul').hide();	
	jQuery(targetid + 'ul.simplemenuclick > li .icon').click(function() {
		jQuery(targetid + 'ul.simplemenuclick > li .child').stop(true, true).slideUp();
		jQuery(targetid + 'ul.simplemenuclick > li').children('.icon').css("background-position","0 0");	
		
		jQuery(this).parent().children('.child').stop(true, true).slideDown();
		jQuery(this).css({
			"background-position":"0 -36px"
		});
	});
	
	jQuery(targetid + 'ul.simplemenuclick > li.current .icon').click();
};