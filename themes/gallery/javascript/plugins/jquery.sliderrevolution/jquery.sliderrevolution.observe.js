ff.cms.fn.sliderrevolution = function(targetid) {
    var targetid = targetid;
    if(targetid.length > 0)
        targetid = targetid + " ";

    if(jQuery(targetid + '.sliderrevolution').length > 0) {
        ff.pluginLoad("jQuery.fn.revolution.tools", "/themes/library/plugins/jquery.sliderrevolution/jquery.themepunch.tools.min.js", function() {
        ff.pluginLoad("jQuery.fn.revolution", "/themes/library/plugins/jquery.sliderrevolution/jquery.themepunch.revolution.min.js", function() {
            jQuery(targetid + ".sliderrevolution").closest("LI").attr("data-transition", "fade");
            jQuery(targetid + ".sliderrevolution").closest("UL").wrap('<div class="revolution" />');
            jQuery(targetid + '.sliderrevolution').closest("UL").parent().revolution ({
				delay:9000,								/*The time one slide stays on the screen in Milliseconds (Default: 9000)*/
				startheight:500,						/*Basic Height of the Slider in the desktop resolution in pixel, other screen sizes will be calculated from this (Default: 490)*/
				startwidth:960,							/* Basic Width of the Slider in the desktop resolution in pixel, other screen sizes will be calculated from this (Default: 890) */
				fullScreenAlignForce:"off",				/*  */
				autoHeight:"off",						/*  */

				hideThumbs:200,							/* Time after that the Thumbs will be hidden(Default: 200) */

				thumbWidth:100,							/* Thumb With and Height and Amount (only if navigation Tyope set to thumb !)*/
				thumbHeight:50,							/*  */
				thumbAmount:3,							/*  */

				navigationType:"bullet",				/* Display type of the navigation bar -> bullet, thumb, none */
				navigationArrows:"solo",				/* Display position of the Navigation Arrows -> nextto, solo, none */

				hideThumbsOnMobile:"off",				/*  */
				hideBulletsOnMobile:"off",				/*  */
				hideArrowsOnMobile:"off",				/*  */
				hideThumbsUnderResoluition:0,			/*  */

				navigationStyle:"round",				/* Look of the navigation bullets -> round,square,navbar,round-old,square-old,navbar-old, or any from the list in the docu (choose between 50+ different item), */

				navigationHAlign:"center",				/* Vertical Align top,center,bottom */
				navigationVAlign:"bottom",				/* Horizontal Align left,center,right */
				navigationHOffset:0,
				navigationVOffset:20,

				soloArrowLeftHalign:"left",
				soloArrowLeftValign:"center",
				soloArrowLeftHOffset:20,
				soloArrowLeftVOffset:0,

				soloArrowRightHalign:"right",
				soloArrowRightValign:"center",
				soloArrowRightHOffset:20,
				soloArrowRightVOffset:0,

				keyboardNavigation:"on",

				touchenabled:"on",						/* Enable Swipe Function : on/off */
				onHoverStop:"on",						/* Stop Banner Timet at Hover on Slide on/off */


				stopAtSlide:-1,							/* Stop Timer if Slide "x" has been Reached. If stopAfterLoops set to 0, then it stops already in the first Loop at slide X which defined. -1 means do not stop at any slide. stopAfterLoops has no sinn in this case. */
				stopAfterLoops:-1,						/* Stop Timer if All slides has been played "x" times. IT will stop at THe slide which is defined via stopAtSlide:x, if set to -1 slide never stop automatic */

				hideCaptionAtLimit:0,					/* It Defines if a caption should be shown under a Screen Resolution ( Basod on The Width of Browser) */
				hideAllCaptionAtLimit:0,				/* Hide all The Captions if Width of Browser is less then this value */
				hideSliderAtLimit:0,					/* Hide the whole slider, and stop also functions if Width of Browser is less than this value */

				shadow:0,								/* 0 = no Shadow, 1,2,3 = 3 Different Art of Shadows  (No Shadow in Fullwidth Version !) */
				fullWidth:"off",						/* Turns On or Off the Fullwidth Image Centering in FullWidth Modus */
				fullScreen:"off",
				minFullScreenHeight:0,					/* The Minimum FullScreen Height */
				fullScreenOffsetContainer:"",
				dottedOverlay:"none",					/* twoxtwo, threexthree, twoxtwowhite, threexthreewhite */

				forceFullWidth: "on"					/* Force The FullWidth */
            });
        }, true);
        });
    }
};