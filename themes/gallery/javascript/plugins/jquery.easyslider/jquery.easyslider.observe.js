ff.cms.fn.easyslider = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	ff.load("jquery.plugins.easyslider", function() {	
        jQuery(targetid + ".easyslider").closest("UL").wrap('<div class="easy-wrap" />');
		jQuery(targetid + ".easyslider").closest("UL").parent().easySlider({
			prevId: 		'prevBtn',
			prevText: 		'Previous',
			nextId: 		'nextBtn',	
			nextText: 		'Next',
			controlsShow:	true,
			controlsBefore:	'',
			controlsAfter:	'',	
			controlsFade:	true,
			firstId: 		'firstBtn',
			firstText: 		'First',
			firstShow:		false,
			lastId: 		'lastBtn',	
			lastText: 		'Last',
			lastShow:		false,				
			vertical:		false,
			speed: 			800,
			auto:			true,
			pause:			2000,
			continuous:		true, 
			numeric: 		false,
			numericId: 		'controls',
			hoverPause:     false
		});
	});
};