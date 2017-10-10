ff.cms.fn.fancybox = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	if(jQuery(targetid + ".fancybox").length > 0) {
		ff.pluginLoad("jquery.fancybox", "/themes/library/plugins/jquery.fancybox/jquery.fancybox.js", function() {	
			
		    jQuery(targetid + ".fancybox").attr("rel","fancybox");
			if(jQuery("a.middlegallery").length > 0) {
				jQuery("a.middlegallery img").each(function() {
					jQuery(this).parent().attr("rel", "fancybox");   
					/*if(jQuery(this).attr("alt") != jQuery(targetid + "a.fancybox img").attr("alt")) {
						jQuery(this).parent().attr("rel", "fancybox");    
					} else {
						jQuery(this).parent().removeAttr("rel");
						
					}*/
				});
			}
			var francyFirst = true,
				IE =  navigator.userAgent.match(/msie/i),
				isTouch		= document.createTouch !== undefined;
			
			
			jQuery("a[rel^='fancybox']").fancybox({
					padding : 15,
					margin  : 20,

					width     : 800,
					height    : 600,
					minWidth  : 100,
					minHeight : 100,
					maxWidth  : 9999,
					maxHeight : 9999,
					pixelRatio: 1, /* Set to 2 for retina display support*/

					autoSize   : true,
					autoHeight : false,
					autoWidth  : false,

					autoResize  : true,
					autoCenter  : !isTouch,
					fitToView   : true,
					aspectRatio : false,
					topRatio    : 0.5,
					leftRatio   : 0.5,

					scrolling : 'auto', /* 'auto', 'yes' or 'no'*/
					wrapCSS   : '',

					arrows     : true,
					closeBtn   : true,
					closeClick : false,
					nextClick  : false,
					mouseWheel : true,
					autoPlay   : false,
					playSpeed  : 3000,
					preload    : 3,
					modal      : false,
					loop       : true,

					ajax  : {
						dataType : 'html',
						headers  : { 'X-fancyBox': true }
					},
					iframe : {
						scrolling : 'auto',
						preload   : true
					},
					swf : {
						wmode: 'transparent',
						allowfullscreen   : 'true',
						allowscriptaccess : 'always'
					},

					keys  : {
						next : {
							13 : 'left', /* enter */
							34 : 'up',   /* page down */
							39 : 'left', /* right arrow */
							40 : 'up'    /* down arrow */
						},
						prev : {
							8  : 'right',  /* backspace */
							33 : 'down',   /* page up */
							37 : 'right',  /* left arrow */
							38 : 'down'    /* up arrow */
						},
						close  : [27], /* escape key */
						play   : [32], /* space - start/stop slideshow */
						toggle : [70]  /* letter "f" - toggle fullscreen */
					},

					direction : {
						next : 'left',
						prev : 'right'
					},

					scrollOutside  : true,

					/* Override some properties */
					index   : 0,
					type    : null,
					href    : null,
					content : null,
					title   : null,

					 /* HTML templates */
					tpl: {
						wrap     : '<div class="fancybox-wrap" tabIndex="-1"><div class="fancybox-skin"><div class="fancybox-outer"><div class="fancybox-inner"></div></div></div></div>',
						image    : '<img class="fancybox-image" src="{href}" alt="" />',
						iframe   : '<iframe id="fancybox-frame{rnd}" name="fancybox-frame{rnd}" class="fancybox-iframe" frameborder="0" vspace="0" hspace="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen' + (IE ? ' allowtransparency="true"' : '') + '></iframe>',
						error    : '<p class="fancybox-error">The requested content cannot be loaded.<br/>Please try again later.</p>',
						closeBtn : '<a title="Close" class="fancybox-item fancybox-close" href="javascript:;"></a>',
						next     : '<a title="Next" class="fancybox-nav fancybox-next" href="javascript:;"><span></span></a>',
						prev     : '<a title="Previous" class="fancybox-nav fancybox-prev" href="javascript:;"><span></span></a>'
					},

					/* Properties for each animation type */
					/* Opening fancyBox */
					openEffect  : 'fade', /* 'elastic', 'fade' or 'none' */
					openSpeed   : 250,
					openEasing  : 'swing',
					openOpacity : true,
					openMethod  : 'zoomIn',

					/* Closing fancyBox */
					closeEffect  : 'fade', // 'elastic', 'fade' or 'none'
					closeSpeed   : 250,
					closeEasing  : 'swing',
					closeOpacity : true,
					closeMethod  : 'zoomOut',

					/* Changing next gallery item */
					nextEffect : 'fade', /* 'elastic', 'fade' or 'none' */
					nextSpeed  : 250,
					nextEasing : 'swing',
					nextMethod : 'changeIn',

					/* Changing previous gallery item */
					prevEffect : 'fade', /* 'elastic', 'fade' or 'none' */
					prevSpeed  : 250,
					prevEasing : 'swing',
					prevMethod : 'changeOut',

					/* Enable default helpers */
					helpers : {
						overlay : true,
						title   : true
					},

					/* Callbacks */
					onCancel     : $.noop, /* If canceling */
					beforeLoad   : $.noop, /* Before loading */
					afterLoad    : $.noop, /* After loading */
					beforeShow   : $.noop, /* Before changing in current item */
					afterShow    : $.noop, /* After opening */
					beforeChange : $.noop, /* Before changing gallery item */
					beforeClose  : $.noop, /* Before closing */
					afterClose   : $.noop
	/*				, 'onStart'		: function(selArr, selIndex, selOpt) {
						if(francyFirst) {
							var altImageselArr = new Array();
							for(var i=0; i<selArr.length; i++) {
								var currImage = jQuery(selArr[i]).children("img").attr("alt");
								
								if(altImageselArr[currImage] === undefined) {
									altImageselArr[currImage] = i;
								} else {
									selArr.splice(i, 1);
									i = i - 1;
								}
							}
							francyFirst = false;
						}
						
					}*/
			});
		});
	}	
};
