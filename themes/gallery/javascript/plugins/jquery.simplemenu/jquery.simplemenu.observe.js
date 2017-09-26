ff.cms.fn.simplemenu = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
		
	jQuery(targetid + 'ul.simplemenu li ul').parent().prepend('<a href="javascript:void(0);" class="icon"></a>');
	jQuery(targetid + 'ul.simplemenu li ul').hide();
	jQuery(targetid + 'ul.simplemenu li.item').stop(true, true).hover(function() {
			jQuery(this).children('.child').stop(true, true).slideDown();
			jQuery(this).children('.icon').css("background-position","2px -36px");
	},function(){
		jQuery(this).children('.child').stop(true, true).slideUp();
		jQuery(this).children('.icon').css("background-position","0 0");		
	});
	
	/* calcolo posizione sottomenu */
	
	var rightNow = jQuery(targetid + ".simplemenu .item .child .item").width();
	jQuery(targetid + ".simplemenu .item .child .item .child").css("right", -rightNow);
};