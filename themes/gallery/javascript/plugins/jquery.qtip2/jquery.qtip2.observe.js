ff.cms.fn.qtip = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
	/*css*/
	ff.load("jquery.plugins.qtip", function() {
		jQuery( targetid + ".qtip").qtip({  
		        prerender: FALSE,
				id: FALSE,
				overwrite: TRUE,
				suppress: TRUE,
				content: {
					text: TRUE,
					attr: 'title',
					title: {
						text: FALSE,
						button: FALSE
					}
				},
				position: {
					my: 'top left',
					at: 'bottom right',
					target: FALSE,
					container: FALSE,
					viewport: FALSE,
					adjust: {
						x: 0, y: 0,
						mouse: TRUE,
						resize: TRUE,
						method: 'flip flip'
					},
					effect: function(api, pos, viewport) {
						$(this).animate(pos, {
							duration: 200,
							queue: FALSE
						});
					}
				},
				show: {
					target: FALSE,
					event: 'mouseenter',
					effect: TRUE,
					delay: 90,
					solo: FALSE,
					ready: FALSE,
					autofocus: FALSE
				},
				hide: {
					target: FALSE,
					event: 'mouseleave',
					effect: TRUE,
					delay: 0,
					fixed: FALSE,
					inactive: FALSE,
					leave: 'window',
					distance: FALSE
				},
				style: {
					classes: '',
					widget: FALSE,
					width: FALSE,
					height: FALSE,
					def: TRUE
				},
				events: {
					render: NULL,
					move: NULL,
					show: NULL,
					hide: NULL,
					toggle: NULL,
					visible: NULL,
					hidden: NULL,
					focus: NULL,
					blur: NULL
				}
		});
	});
};